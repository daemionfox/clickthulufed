<?php

namespace App\Controller;

use App\Entity\RegistrationCode;
use App\Entity\User;
use App\Exceptions\ClickthuluException;
use App\Form\RegistrationFormType;
use App\Helpers\SettingsHelper;
use App\Security\EmailVerifier;
use App\Security\LoginAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, LoginAuthenticator $authenticator, EntityManagerInterface $entityManager, ?string $email): Response
    {
        $settings = SettingsHelper::init($entityManager);
        $email = $request->query->get('email');
        $code = $request->query->get('code');
        $autoActivate = (int)$settings->get('allow_user_signup');
        $autoActivate = $autoActivate > 0;

        $user = $this->getUser();

        if (!empty($user)) {
            return new RedirectResponse($this->generateUrl("app_profile"));
        }
        $user = new User();

        if (!empty($email) && !empty($code)) {

            /**
             * @var RegistrationCode $regcode
             */
            $regcode = $entityManager->getRepository(RegistrationCode::class)->findOneBy(['code' => $code]);
            if ($regcode->isActivated() === false && $regcode->getExpireson() >= new \DateTime() && $regcode->getEmail() === $email) {
                $regcode->setActivated(true);
                $entityManager->persist($regcode);
                $entityManager->flush();
                $autoActivate = true; // Check bool
            }

        }

        if(!$autoActivate) {
            return $this->render('error.html.twig', [
                'message' => 'Registration is closed to the public.'
            ]);
        }


        if (!empty($email)) {
            $user->setEmail($email);
        }
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );



            $user->setRoles(['ROLE_USER']);
            $user->setActive($autoActivate);
            $user->setDeleted(false);
            $entityManager->persist($user);
            $entityManager->flush();

            $emailfrom = $settings->get('email_from_address');
            if (empty($emailfrom)) {
                throw new ClickthuluException("Email From address is not configured.");
            }

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address($emailfrom, $settings->get('email_from_name', $emailfrom)))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            // do anything else you need here, like send an email

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/acceptinvite/{code}', name: 'app_verify_invite')]
    public function verifyUserInvite(string $code, Request $request, EntityManagerInterface $entityManager): Response
    {
        /**
         * @var RegistrationCode $regcode
         */
        $regcode = $entityManager->getRepository(RegistrationCode::class)->findOneBy(['code' => $code]);
        if ($regcode->isActivated() === false && $regcode->getExpireson() >= new \DateTime()) {
            $regcode->setActivated(true);
            $entityManager->persist($regcode);
            $entityManager->flush();
            return new RedirectResponse($this->generateUrl('app_register', ['email' => $regcode->getEmail(), 'code' => $code]));
        }

        return $this->render('error.html.twig', ['message' => "We're sorry.  The invite code you used is either invalid or expired."]);
    }


    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_contentfeed');
    }
}

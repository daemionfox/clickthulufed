<?php

namespace App\Controller;

use App\Entity\Comic;
use App\Entity\ThemeFile;
use App\Entity\User;
use App\Form\LayoutType;
use App\Form\ThemeDuplicationType;
use App\Form\ThemeFileType;
use App\Helpers\SettingsHelper;
use App\Traits\ComicOwnerTrait;
use App\Traits\MediaPathTrait;
use App\Traits\RecursiveCopyTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ThemeController extends AbstractController
{
    use MediaPathTrait;
    use ComicOwnerTrait;
    use RecursiveCopyTrait;

    #[Route('/themes/{slug}', name: 'app_themelist')]
    public function themeList(EntityManagerInterface $entityManager, string $slug)
    {
        $settings = SettingsHelper::init($entityManager);

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /**
         * @var User $user
         */
        $user = $this->getUser();
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug] );

        if (!$this->comicUserMatch($user, $comic)) {
            $this->addFlash("error", "You do not have permission to manage themes for this comic");
        }
        $themes = $this->getThemeData($settings, $user->getUsername(), $comic->getSlug());

        return $this->render('themes/themelist.html.twig', ['comic' => $comic, 'themes' => $themes]);
    }

    #[Route('/themes/{slug}/copy/{type}/{theme}', name: 'app_themecopy')]
    public function copyTheme(EntityManagerInterface $entityManager, Request $request, string $slug, string $type, string $theme): Response
    {
        $settings = SettingsHelper::init($entityManager);

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /**
         * @var User $user
         */
        $user = $this->getUser();
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug] );

        if (!$this->comicUserMatch($user, $comic)) {
            $this->addFlash("error", "You do not have permission to manage themes for this comic");
        }

        $form = $this->createForm(ThemeDuplicationType::class, ['sourcetheme' => "@{$type}/{$theme}"], ['settings' => $settings, 'comic' => $comic]);

        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $formdata = $form->getData();
                $source = $formdata['sourcetheme'];
                $target = $formdata['targettheme'];
                $name = $formdata['targetname'];
                // Create a new directory in the themes folder.
                $customPath = $this->getMediaPath($settings, $comic->getOwner()->getUsername(), $comic->getSlug(), 'themes');
                $targetPath = "{$customPath}/{$target}";
                $sourcePath = $this->getThemePath($settings, $source, $comic);
                mkdir($targetPath, 0755, true);

                // Make a copy of the source to target
                $this->recurseCopy($sourcePath, $targetPath);
                // Generate a new data.json

                $data = json_decode(file_get_contents("{$targetPath}/data.json"));
                unlink("{$targetPath}/data.json");

                $data->theme = $name;
                $data->slug = $target;
                $data->author = $user->getName() ?? $user->getUsername();
                $data->createdon = date("Y-m-d", time());

                file_put_contents("{$targetPath}/data.json", json_encode($data, JSON_PRETTY_PRINT));

                $this->addFlash('info', "Theme Copied to @custom/{$target}");
                return new RedirectResponse($this->generateUrl('app_themelist', ['slug' => $comic->getSlug()]));
            }
        } catch (\Exception $e){
            $err = new FormError($e->getMessage());
            $form->addError($err);
        }




        return $this->render('themes/themecopy.html.twig', [
            'comic' => $comic,
            'themeForm' => $form->createView()
        ]);

    }

    #[Route('/themes/{slug}/check/{theme}', name: 'app_themecheck')]
    public function checkTheme(EntityManagerInterface $entityManager, string $slug, string $theme)
    {
        $settings = SettingsHelper::init($entityManager);

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /**
         * @var User $user
         */
        $user = $this->getUser();
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug] );

        if (!$this->comicUserMatch($user, $comic)) {
            $this->addFlash("error", "You do not have permission to manage themes for this comic");
        }

        $themes = $this->getThemeData($settings, $user->getUsername(), $comic->getSlug());

        foreach($themes['custom'] as $t) {
            if (strtolower($t['slug']) === strtolower($theme)) {
                return new JsonResponse(['status' => 'failed', 'message' => 'Theme slug already exists'], 400);
            }
        }
        return new JsonResponse(['status' => 'passed'], 200);
    }


    #[Route('/themes/{slug}/delete/{theme}', name: 'app_themedelete')]
    public function deleteTheme(EntityManagerInterface $entityManager, Request $request, string $slug, string $theme): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $settings = SettingsHelper::init($entityManager);

        /**
         * @var User $user
         */
        $user = $this->getUser();
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug] );

        if (!$this->comicUserMatch($user, $comic)) {
            $this->addFlash("error", "You do not have permission to manage themes for this comic");
        }

        $target = $this->getThemePath($settings, "@custom/{$theme}", $comic);

        $this->recurseDelete($target);

        $this->addFlash('info', "Theme deleted");

        return new RedirectResponse($this->generateUrl('app_themelist', ['slug' => $comic->getSlug()]));

    }


    #[Route('/themes/{slug}/edit/{theme}', name: 'app_editthemefile1')]
    #[Route('/themes/{slug}/editfile/{theme}/{file}', name: 'app_editthemefile')]
    public function editTheme(EntityManagerInterface $entityManager, Request $request, string $slug, string $theme, ?string $file): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $settings = SettingsHelper::init($entityManager);

        /**
         * @var User $user
         */
        $user = $this->getUser();
        /**
         * @var Comic $comic
         */
        $comic = $entityManager->getRepository(Comic::class)->findOneBy(['slug' => $slug] );

        if (!$this->comicUserMatch($user, $comic)) {
            $this->addFlash("error", "You do not have permission to manage themes for this comic");
        }

        $target = $this->getThemePath($settings, "@custom/{$theme}", $comic);
        $files = glob("{$target}/*.twig");
        $files = array_map('basename', $files);
        $themedata = json_decode(file_get_contents("{$target}/data.json"));
        $themeent = new ThemeFile();
        $themeent->setTheme($theme);

        if ($file) {
            $data = file_get_contents("{$target}/{$file}");
            $themeent->setFilename($file)->setData($data);
        }

        $form = $this->createForm(ThemeFileType::class, $themeent);
        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {

                $data = $form->getData();

                $filename = $data->getFilename();
                $contents = $data->getData();
                file_put_contents("{$target}/{$filename}", $contents);



                $this->addFlash('info', "{$filename} saved at " . date("H:i", time()));


            }
        } catch (\Exception $e){
            $err = new FormError($e->getMessage());
            $form->addError($err);
        }

        return $this->render('themes/edittheme.html.twig', [
            'comic' => $comic,
            'theme' => $themedata,
            'file' => $file,
            'themefiles' => $files,
            'themeForm' => $form->createView()
        ]);


    }

}
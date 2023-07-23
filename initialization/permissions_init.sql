INSERT INTO permissions (name, description, createdon) values
       ('can_create_admin', 'User can create an administrator or promote an existing user to an administrator', NOW()),
       ('can_create_creator', 'User can create a creator or promote an existing user to a creator', NOW()),
       ('can_create_user', 'User can create other users', NOW())),
       ('can_create_comic', 'User can create a new comic', NOW()),
       ('can_request_comic', 'User can submit a new comic to the server', NOW()),
       ('can_approve_comic', 'User can approve a comic submitted to the server', NOW()),
       ('can_moderate_comment', 'User has the ability to moderate comments', NOW())
       ('can_change_federation', 'User has the ability to defederate/refederate other servers', NOW())
;
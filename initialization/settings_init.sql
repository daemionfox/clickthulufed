INSERT INTO settings (setting, value, defaultvalue, modifiedon, type, help, display_name, sourceoptions) values
    ('allow_user_signup', '1', '1', NOW(), 'bool', 'Allow users to register without approval.', 'Open user registration', null),
    ('require_comic_approval', '0', '0', NOW(), 'bool', 'Require approval from admins to activate a comic.', 'Require approval for new comics', null),
    ('server_name', null, null, NOW(), 'string', 'Name of this Server', 'Server Name', null),
    ('server_url', null, null, NOW(), 'string', 'Server\'s URL', 'Server URL', null),
    ('user_storage_path', 'storage', 'storage', NOW(), 'string', 'Path for user storage', 'User Storage Path', null),
--    ('use_ocr_for_transcript', '0', '0', NOW(), 'bool', 'Attempt to use OCR on comic page to get transcript.', 'Use OCR for Transcripts', null),
    ('generate_thumbnails', '1' ,'1', NOW(), 'bool', 'Auto generate thumbnails of uploaded pages.', 'Generate page thumbnails', null),
    ('allow_custom_themes', '1', '1', NOW(), 'bool', 'Allow users to generate custom themes for their comics.', 'Allow custom themes', null),
    ('email_from_name', null, null, NOW(), 'string', 'Name used to send mail', 'From Name', null),
    ('email_from_address', null, null, NOW(), 'string', 'Email address used to send mail', 'From Address', null),
    ('admin_theme', null, null, NOW(), 'fileselect', 'Theme for ClickthuluFed Administration', 'Admin Theme', 'css/adminthemes/*.css')
;
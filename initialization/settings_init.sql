INSERT INTO settings (setting, value, defaultvalue, modifiedon, type) values
    ('allow_user_signup', '1', '1', NOW(), 'bool'),
    ('require_comic_approval', '0', '0', NOW(), 'bool'),
    ('server_url', null, null, NOW(), 'string'),
    ('comic_page_path', 'comicpages', 'comicpages', NOW(), 'string'),
    ('use_ocr_for_transcript', '0', '0', NOW(), 'bool'),
    ('generate_thumbnails', '1' ,'1', NOW(), 'bool')
;
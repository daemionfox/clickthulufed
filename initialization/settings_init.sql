INSERT INTO settings (setting, value, defaultvalue, modifiedon) values
    ('allow_user_signup', '1', '1', NOW()),
    ('require_comic_approval', '0', '0', NOW()),
    ('server_url', null, null, NOW()),
    ('comic_page_path', 'comicpages', 'comicpages', NOW())
;
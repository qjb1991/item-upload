<?php
/**
 * Created by item-upload
 * User: bibo
 * Date: 2020-01-11
 * Time: 15:38
 */

define('TABLE_UPLOAD_PROJECT', 'iu_upload_project');
define('TABLE_FILE', 'iu_file');

define('IS_WIN', strpos(PHP_OS, 'WIN') !== false);

define('KEY_SECRET_STATE_ON', 1);
define('KEY_SECRET_STATE_OFF', 0);

define('DEF_ALG', 'HS256');
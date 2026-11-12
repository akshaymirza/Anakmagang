<?php
/**
 * partials/favicon.php
 * Menyisipkan tag <link> favicon untuk ikon tab browser di seluruh halaman PHP.
 */
$_fav_project_root = str_replace('\\', '/', realpath(dirname(__DIR__)) ?: dirname(__DIR__));
$_fav_script_file  = str_replace('\\', '/', realpath($_SERVER['SCRIPT_FILENAME'] ?? '') ?: ($_SERVER['SCRIPT_FILENAME'] ?? ''));

$_fav_prefix   = '';
$_fav_root_cmp = rtrim($_fav_project_root, '/') . '/';
$_fav_match    = (DIRECTORY_SEPARATOR === '\\')
    ? (stripos($_fav_script_file, $_fav_root_cmp) === 0)
    : (strpos($_fav_script_file, $_fav_root_cmp) === 0);

if ($_fav_script_file !== '' && $_fav_match) {
    $_fav_rel    = substr($_fav_script_file, strlen($_fav_root_cmp));
    $_fav_depth  = substr_count($_fav_rel, '/');
    $_fav_prefix = str_repeat('../', $_fav_depth);
} else {
    $_fav_dir        = basename(dirname($_fav_script_file));
    $_fav_parent_dir = basename(dirname(dirname($_fav_script_file)));
    if ($_fav_dir === 'Login' && $_fav_parent_dir === 'admin') {
        $_fav_prefix = '../../';
    } elseif (in_array($_fav_dir, ['admin', 'Login', 'partials'], true)) {
        $_fav_prefix = '../';
    } elseif ($_fav_dir === 'Certificate') {
        $_fav_prefix = '../../';
    }
}

$_fav_fs_path = __DIR__ . '/../uploads/Icon/Anak_Magang_Icon.jpg.jpeg';
$_fav_version = file_exists($_fav_fs_path) ? ('?v=' . filemtime($_fav_fs_path)) : '';
$_fav_href    = htmlspecialchars($_fav_prefix . 'uploads/Icon/Anak_Magang_Icon.jpg.jpeg' . $_fav_version, ENT_QUOTES, 'UTF-8');
?>
    <link rel="icon" type="image/jpeg" href="<?php echo $_fav_href; ?>"/>
    <link rel="shortcut icon" type="image/jpeg" href="<?php echo $_fav_href; ?>"/>
    <link rel="apple-touch-icon" href="<?php echo $_fav_href; ?>"/>


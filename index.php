<?php
// ---------------------------
// YAML Static Site Builder (Standalone)
// ---------------------------

// Include Spyc parser (make sure spyc.php is in the same folder)
require __DIR__ . '/spyc.php';

// ---------------------------
// CONFIG
// ---------------------------
define('SITE_ROOT', __DIR__);
define('SITE_YAML', SITE_ROOT . '/site.yml');
define('PAGES_DIR', SITE_ROOT . '/pages');
define('THEMES_DIR', SITE_ROOT . '/themes');

// ---------------------------
// UTILITIES
// ---------------------------
function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES,'UTF-8'); }

function style_to_string(array $style=[]){
    $s='';
    foreach($style as $k=>$v) $s .= esc($k) . ':' . esc($v) . ';';
    return $s ? " style=\"$s\"" : '';
}

// ---------------------------
// ELEMENT / BLOCK RENDERER
// ---------------------------
function render_element($node): string {
    if(is_string($node)) return esc($node);
    $tag = $node['tag'] ?? 'div';
    $attrs = '';
    foreach(($node['attrs'] ?? []) as $k=>$v) $attrs .= ' '.esc($k).'="'.esc($v).'"';
    $attrs .= style_to_string($node['style'] ?? []);
    $html = "<$tag$attrs>";
    foreach(($node['children'] ?? []) as $child) $html .= render_element($child);
    $html .= "</$tag>";
    return $html;
}

function render_block($block): string {
    $type = $block['type'] ?? 'text';
    switch($type){
        case 'text': return "<p>".esc($block['content'] ?? '')."</p>";
        case 'markdown': return "<div>".esc($block['content'] ?? '')."</div>";
        case 'hero': return "<section class='hero'><h1>".esc($block['title'] ?? '')."</h1><p>".esc($block['subtitle'] ?? '')."</p></section>";
        case 'button': return "<a class='btn' href='".esc($block['href'] ?? '#')."'>".esc($block['label'] ?? 'Button')."</a>";
        case 'element': return render_element($block);
        default: return "<!-- unknown block -->";
    }
}

// ---------------------------
// LOAD SITE & THEME
// ---------------------------
$site = Spyc::YAMLLoad(SITE_YAML);
$themeName = $site['theme'] ?? 'default';
$theme = Spyc::YAMLLoad(THEMES_DIR."/{$themeName}/theme.yml");

// ---------------------------
// DETERMINE CURRENT PAGE
// ---------------------------
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path,'/') ?: '/';
$pageFile = $path === '/' ? PAGES_DIR.'/index.yml' : PAGES_DIR.$path.'.yml';

if(!file_exists($pageFile)){
    http_response_code(404);
    echo "404 – Page not found";
    exit;
}

$page = Spyc::YAMLLoad($pageFile);

// ---------------------------
// RENDER HTML
// ---------------------------
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?= esc($page['title'] ?? $site['name']) ?></title>
<link rel="stylesheet" href="<?= esc($theme['style'] ?? '') ?>">
<style>
/* Basic styling */
body{font-family:system-ui,sans-serif;padding:40px;margin:0;background:#fafafa;color:#333;}
header h2{margin:0 0 10px;}
footer{margin-top:40px;text-align:center;color:#666;}
.hero{padding:60px 20px;text-align:center;background-color:#e0f7fa;}
a.btn{display:inline-block;padding:10px 20px;background:#000;color:#fff;text-decoration:none;border-radius:4px;}
a.btn:hover{background:#333;}
.card{border:1px solid #ccc;border-radius:6px;padding:20px;margin:10px;background:#fff;}
.grid{display:flex;flex-wrap:wrap;gap:20px;}
.grid .card{flex:1 1 calc(33% - 20px);box-sizing:border-box;}
@media (max-width:768px){.grid .card{flex:1 1 100%;}}
img.responsive{max-width:100%;height:auto;border-radius:6px;}
</style>
</head>
<body>
<header>
<h2><?= esc($site['name'] ?? '') ?></h2>
<hr>
</header>
<main>
<?php
foreach($page['blocks'] ?? [] as $block){
    echo render_block($block);
}
?>
</main>
<footer>
<hr>
<small><?= esc($site['footer'] ?? '') ?></small>
</footer>
</body>
</html>

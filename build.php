<?php
// ---------------------------
// YAML Static Site Builder (Single-Core, Safe)
// ---------------------------

require __DIR__.'/spyc.php';

// ---------------------------
// CONFIG
// ---------------------------
define('PAGES_DIR', __DIR__.'/pages');
define('DIST_DIR', __DIR__.'/dist');
define('SITE_YAML', __DIR__.'/site.yml');
define('THEMES_DIR', __DIR__.'/themes');

if(!is_dir(DIST_DIR)) mkdir(DIST_DIR, 0777, true);

// ---------------------------
// RENDER FUNCTIONS
// ---------------------------
function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES,'UTF-8'); }

function style_to_string(array $style=[]){ 
    $s=''; foreach($style as $k=>$v) $s .= esc($k).':'.esc($v).';'; 
    return $s? " style=\"$s\"":''; 
}

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
        case 'hero': return "<section class='hero'><h1>".esc($block['title'] ?? '')."</h1><p>".esc($block['subtitle'] ?? '')."</p></section>";
        case 'button': return "<a class='btn' href='".esc($block['href'] ?? '#')."'>".esc($block['label'] ?? 'Button')."</a>";
        case 'element': return render_element($block);
        default: return "<!-- unknown block -->";
    }
}

function render_layout($site,$page,$theme): string {
    ob_start(); ?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?= esc($page['title'] ?? $site['name']) ?></title>
<link rel="stylesheet" href="<?= esc($theme['style'] ?? '') ?>">
<style>
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
<?php foreach($page['blocks'] ?? [] as $block) echo render_block($block); ?>
</main>
<footer>
<hr>
<small><?= esc($site['footer'] ?? '') ?></small>
</footer>
</body>
</html>
<?php
    return ob_get_clean();
}

// ---------------------------
// LOAD SITE & THEME
// ---------------------------
$site = Spyc::YAMLLoad(SITE_YAML);
$themeName = $site['theme'] ?? 'default';
$theme = Spyc::YAMLLoad(THEMES_DIR."/{$themeName}/theme.yml");

// ---------------------------
// BUILD PAGES (SEQUENTIAL)
// ---------------------------
$allPages = glob(PAGES_DIR.'/*.yml');
$startTime = microtime(true);

foreach($allPages as $pageFile){
    $page = Spyc::YAMLLoad($pageFile);
    $filename = basename($pageFile,'.yml').'.html';
    file_put_contents(DIST_DIR.'/'.$filename, render_layout($site,$page,$theme));
    echo "Built: $filename\n";
}

$elapsed = microtime(true)-$startTime;
echo "✅ Build complete in ".round($elapsed,3)." seconds.\n";

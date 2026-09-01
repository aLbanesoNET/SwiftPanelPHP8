Aurora — client-area theme (CSS only)

The client-area controllers include templates/default/*.php directly, but the
page <head> links  templates/<TEMPLATE>/style.css , so this folder only needs
style.css + images/ to reskin the whole client area.

Enabled by  define('TEMPLATE', 'aurora')  in ../../include.php
Revert with define('TEMPLATE', 'default').

images/ is a copy of templates/default/images (the CSS and a few controllers
reference templates/<TEMPLATE>/images/...). Delete this folder's style.css and
images/ and set TEMPLATE back to 'default' to fully remove the theme.

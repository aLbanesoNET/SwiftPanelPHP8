<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php if($page!=='login'): ?></div></main><footer class="av-footer">Swift Panel administration <span><?= defined('VERSION')?htmlspecialchars(VERSION):'PHP 8' ?></span></footer></div><?php else: ?></div></main><?php endif; ?></body></html>

<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php if ($LOGGED_IN ?? false): ?>
	</div></main><footer class="vx-footer"><span>Vertex workspace · <?= date('Y') ?></span><span>Swift Panel <?= defined('VERSION') ? htmlspecialchars(VERSION) : 'PHP 8' ?></span></footer></div>
<?php else: ?>
</main></div>
<?php endif; ?>
<script>document.querySelectorAll('.message, .success, .error').forEach(function(e){e.classList.add('vx-alert-box');});</script>
</body></html>

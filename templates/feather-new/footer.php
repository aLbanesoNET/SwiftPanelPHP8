<?php if (!defined('SITENAME')) { http_response_code(403); exit('Forbidden'); } ?>
<?php $LOGGED_IN = $LOGGED_IN ?? false; ?>
		</div><!-- /#container -->
	</main>
<?php if ($LOGGED_IN): ?>
	<footer id="footer">
		<span>&copy; <?= date('Y') ?> <?= htmlspecialchars($SITE_NAME ?? SITENAME) ?></span>
		<span class="foot-sep">/</span>
		<span>Swift Panel &mdash; PHP 8</span>
	</footer>
</div><!-- /#page -->
<?php else: ?>
</div><!-- /#page -->
<?php endif; ?>
</body>
</html>

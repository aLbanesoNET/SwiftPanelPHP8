<?php

/*
 * Minimal drop-in replacement for the small slice of the classic PHPMailer API
 * this panel actually uses (IsMail + AddAddress/AddBCC + From/FromName/Subject/
 * Body + Send). It sends through PHP's mail() — configure a working MTA / the
 * `sendmail_path` php.ini setting on the server. Swap this file for the real
 * PHPMailer library if you need SMTP.
 */
class PHPMailer
{
	public string $From = '';
	public string $FromName = '';
	public string $Subject = '';
	public string $Body = '';
	public bool $IsHTML = false;

	private array $to = [];
	private array $bcc = [];

	public function IsMail(): void {}

	public function IsHTML(bool $html = true): void
	{
		$this->IsHTML = $html;
	}

	public function AddAddress(string $address, string $name = ''): void
	{
		$this->to[] = $this->formatAddress($address, $name);
	}

	public function AddBCC(string $address, string $name = ''): void
	{
		if (trim($address) !== '') {
			$this->bcc[] = $this->formatAddress($address, $name);
		}
	}

	public function Send(): bool
	{
		if (!$this->to) {
			return false;
		}

		$headers = [];
		if ($this->From !== '') {
			$headers[] = 'From: ' . $this->formatAddress($this->From, $this->FromName);
			$headers[] = 'Reply-To: ' . $this->stripHeaderInjection($this->From);
		}
		if ($this->bcc) {
			$headers[] = 'Bcc: ' . implode(', ', $this->bcc);
		}
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-Type: text/' . ($this->IsHTML ? 'html' : 'plain') . '; charset=UTF-8';
		$headers[] = 'X-Mailer: SwiftPanel';

		$body = $this->IsHTML ? $this->Body : str_replace("\r\n", "\n", $this->Body);

		return @mail(
			implode(', ', $this->to),
			$this->encodeHeader($this->stripHeaderInjection($this->Subject)),
			$body,
			implode("\r\n", $headers)
		);
	}

	private function formatAddress(string $address, string $name = ''): string
	{
		$address = $this->stripHeaderInjection(trim($address));
		$name = $this->stripHeaderInjection(trim($name));

		return $name !== ''
			? $this->encodeHeader($name) . ' <' . $address . '>'
			: $address;
	}

	/** Drop CR/LF and other control bytes so a poisoned name/subject/address can't smuggle extra mail headers. */
	private function stripHeaderInjection(string $value): string
	{
		return trim(preg_replace('/[\x00-\x1F\x7F]+/', ' ', $value) ?? '');
	}

	private function encodeHeader(string $value): string
	{
		return preg_match('/[^\x20-\x7e]/', $value)
			? '=?UTF-8?B?' . base64_encode($value) . '?='
			: $value;
	}
}

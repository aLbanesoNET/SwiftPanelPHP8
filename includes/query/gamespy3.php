<?php
class Query_Protocol_gamespy3 extends Query_Protocol
{
	public function status()
	{
		$this->info();
		while ($this->p->getLength() && ($type = $this->p->readInt8())) {
			if($type == 1) {
				$this->getSub("players");
			} elseif($type == 2) {
				$this->getSub("teams");
			} else {
				$this->getSub("players");
				$this->getSub("teams");
			}
		}
	}
	private function info()
	{
		while ($this->p->getLength()) {
			$var = $this->p->readString();
			if(empty($var)) {
				break;
			}
			$this->r->add($var, $this->p->readString());
		}
	}
	private function getSub($type)
	{
		while ($this->p->getLength()) {
			$header = $this->p->readString();
			if($header == "") {
				break;
			}
			$this->p->skip();
			while ($this->p->getLength()) {
				$value = $this->p->readString();
				if($value === "") {
					break;
				}
				$this->r->addSub($type, $header, $value);
			}
		}
	}
	public function preprocess($packets)
	{
		$result = array();
		foreach ($packets as $packet) {
			$p = new GameQ_Buffer($packet);
			$p->skip(14);
			$cur_packet = $p->readInt16();
			$result[$cur_packet] = $p->getBuffer();
		}
		ksort($result);
		$result = array_values($result);
		$i = 0;
		for ($x = count($result); $i < $x - 1; $i++) {
			$fst = substr($result[$i], 0, 0 - 1);
			$snd = $result[$i + 1];
			$fstvar = substr($fst, strrpos($fst, "") + 1);
			$snd = substr($snd, strpos($snd, "") + 2);
			$sndvar = substr($snd, 0, strpos($snd, ""));
			if(strpos($sndvar, $fstvar) !== false) {
				$result[$i] = preg_replace("" . "#(\\x00[^\\x00]+\\x00)\$#", "", $result[$i]);
			}
		}
		return implode("", $result);
	}
	public function parseChallenge($packet)
	{
		$this->p->skip(5);
		$cc = (int) $this->p->readString();
		$x = pack("H*", sprintf("%08X", $cc));
		return sprintf($packet, $x);
	}
}

?>
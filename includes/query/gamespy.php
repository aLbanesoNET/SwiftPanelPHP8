<?php
class Query_Protocol_gamespy extends Query_Protocol
{
	public function status()
	{
		$this->header();
		while ($this->p->getLength()) {
			$key = $this->p->readString("\\");
			if($key == "final") {
				break;
			}
			$suffix = strrpos($key, "_");
			if($suffix === false || !is_numeric(substr($key, $suffix + 1))) {
				$this->r->add($key, $this->p->readString("\\"));
			} else {
				$this->r->addPlayer(substr($key, 0, $suffix), $this->p->readString("\\"));
			}
		}
	}
	public function players()
	{
		$this->status();
	}
	public function basic()
	{
		$this->status();
	}
	public function info()
	{
		$this->status();
	}
	public function preprocess($packets)
	{
		if(count($packets) == 1) {
			return $packets[0];
		}
		$newpackets = array();
		foreach ($packets as $packet) {
			preg_match("" . "#^(.*)\\\\queryid\\\\([^\\\\]+)(\\\\|\$)#", $packet, $matches);
			if(!isset($matches[1]) || !isset($matches[2])) {
				throw new Query_ParsingException();
			}
			$newpackets[$matches[2]] = $matches[1];
		}
		ksort($newpackets);
		$newpackets = array_values($newpackets);
		return implode("", $newpackets);
	}
	private function header()
	{
		if($this->p->read() !== "\\") {
			throw new Query_ParsingException($this->p);
		}
	}
}

?>
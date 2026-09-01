<?php
class Query_Protocol_gamespy2 extends Query_Protocol
{
	public function status()
	{
		$this->header();
		while ($this->p->getLength()) {
			$this->r->add($this->p->readString(), $this->p->readString());
		}
	}
	public function players()
	{
		$this->header();
		$this->getSub("players");
		$this->getSub("teams");
	}
	private function getSub($type)
	{
		try {
			$this->r->add("num_" . $type, $this->p->readInt8());
		} catch (Query_ParsingException $e) {
			return NULL;
		}
		$varnames = array();
		while ($this->p->getLength()) {
			$varnames[] = str_replace("_", "", $this->p->readString());
			if($this->p->lookAhead() === "") {
				$this->p->skip();
				break;
			}
		}
		if($this->p->lookAhead() == "") {
			$this->p->skip();
		} else {
			while (4 < $this->p->getLength()) {
				foreach ($varnames as $varname) {
					$this->r->addSub($type, $varname, $this->p->readString());
				}
				if($this->p->lookAhead() === "") {
					$this->p->skip();
					break;
				}
			}
		}
	}
	private function header()
	{
		if($this->p->read() !== "") {
			throw new Query_ParsingException($this->p);
		}
		$this->p->read(4);
		if($this->p->lookAhead() == "") {
			$this->p->read();
		}
		if($this->p->readLast() !== "") {
			throw new Query_ParsingException($this->p);
		}
	}
}

?>
<?php
class Query_Protocol_valve extends Query_Protocol
{
	const TYPE_GOLDSOURCE = 9;
	const TYPE_SOURCE = 12;
	public function details()
	{
		$this->p->skip(4);
		$type = $this->p->readInt8();
		if($type == 109) {
			$this->r->add("address", $this->p->readString());
		} else {
			$this->r->add("protocol", $this->p->readInt8());
		}
		$this->r->add("hostname", $this->p->readString());
		$this->r->add("map", $this->p->readString());
		$this->r->add("game_dir", $this->p->readString());
		$this->r->add("game_descr", $this->p->readString());
		if($type != 109) {
			$this->r->add("steamappid", $this->p->readInt16());
		}
		$this->r->add("num_players", $this->p->readInt8());
		$this->r->add("max_players", $this->p->readInt8());
		if($type == 109) {
			$this->r->add("protocol", $this->p->readInt8());
		} else {
			$this->r->add("num_bots", $this->p->readInt8());
		}
		$this->r->add("dedicated", $this->p->read());
		$this->r->add("os", $this->p->read());
		$this->r->add("password", $this->p->readInt8());
		$this->r->add("secure", $this->p->readInt8());
		$this->r->add("version", $this->p->readInt8());
	}
	public function players()
	{
		$this->p->skip(5);
		$this->r->add("num_players", $this->p->readInt8());
		while ($this->p->getLength()) {
			$this->r->addPlayer("id", $this->p->readInt8());
			$this->r->addPlayer("name", $this->p->readString());
			$this->r->addPlayer("score", $this->p->readInt32());
			$this->r->addPlayer("time", $this->p->readFloat32());
		}
	}
	public function rules()
	{
		$this->p->skip(5);
		$count = $this->p->readInt16();
		if($count == 65535) {
			$this->p->skip();
			$count = $this->p->readInt16();
		}
		$this->r->add("num_rules", $count);
		while ($this->p->getLength()) {
			$this->r->add($this->p->readString(), $this->p->readString());
		}
	}
	public function parseChallenge($packet)
	{
		$this->p->skip(5);
		return sprintf($packet, $this->p->read(4));
	}
	public function preprocess($packets)
	{
		$result = array();
		$type = false;
		$compressed = false;
		foreach ($packets as $key => $packet) {
			$p = new Query_Buffer($packet);
			$p->skip(4);
			$peek = $p->lookAhead(12);
			if(substr($peek, 5, 4) == "\xFF\xFF\xFF\xFF") {
				$type = self::TYPE_GOLDSOURCE;
				break;
			}
			if(substr($peek, 8, 4) == "\xFF\xFF\xFF\xFF") {
				$type = self::TYPE_SOURCE;
				break;
			}
			if(3 < $p->getLength() && $p->readInt32() & 2147483648.0) {
				$type = self::TYPE_SOURCE;
				break;
			}
		}
		if($type === false) {
			return $packets[0];
		}
		foreach ($packets as $packet) {
			$p = new Query_Buffer($packet);
			$p->skip(4);
			$request_id = $p->readInt32();
			if($type == self::TYPE_GOLDSOURCE) {
				$byte = $p->readInt8();
				$num_packets = $byte & 15;
				$cur_packet = $byte >> 4 & 15;
			} else {
				$num_packets = $p->readInt8();
				$cur_packet = $p->readInt8();
				if($request_id & 2147483648.0) {
					$compressed = true;
					$packet_decompressed = $p->readInt32();
					$packet_checksum = $p->readInt32();
				}
			}
			$result[$cur_packet] = $p->getBuffer();
		}
		ksort($result);
		if($compressed) {
			if(!function_exists("bzdecompress")) {
				return false;
			}
			$result = array_map("bzdecompress", $result);
		}
		return implode("", $result);
	}
	private function detect($packets)
	{
		foreach ($packets as $packet) {
			$m = preg_match("#^(\\xFE|\\xFF)\\xFF{3}.{5}\\xFF{4}#", $packet);
			if($m) {
				return self::TYPE_GOLDSOURCE;
			}
		}
		return self::TYPE_SOURCE;
	}
}

?>
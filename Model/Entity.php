<?php
	class Entity implements JsonSerializable {
		private int $id = 0;
		private string $descr = "";
		private int $value = 0;

		public function __construct(int $id, string $descr, int $value) {
			$this->id = $id;
			$this->descr = $descr;
			$this->value = $value;
		}
		public static function from_json($json): Entity {
			$obj = json_decode($json, "");
			return new Entity($obj->id, $obj->descr, $obj->value);
		}

		public function get_id(): int { return $this->id; }

        public function get_value(): int { return $this->value; }

		public function set_id($id) { $this->id = $id; }

        public function set_value($value) { $this->value = $value; }

		public function __tostring() { return 
			"{ id: ".$this->id.
			" descr: '".$this->descr."'".
			" value: ".$this->value." }";
		}
		public function to_Html_tr(): string {
			return
				"<tr>".
				"<ti>".$this->id."</ti>".
				"<ti>".$this->descr."</ti>".
				"<ti>".$this->value."</ti>".
				"</tr>";
		}
		public function to_json() {
			return json_encode($this);
		}

	    public function jsonSerialize() {
    	    return get_object_vars($this);
    	}
	}
?>

<?php

namespace Utilities;

class Regexes {
    static string $Email = "^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$";

    static string $Password = <<< REGEXP
.{12,}
REGEXP;

    static string $Username = <<< REGEXP
^[a-zA-Z0-9_-]{4,32}$
REGEXP;


}


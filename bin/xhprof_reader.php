<?php
$content = file_get_contents("/usr/local/phim/phim.xhprof");
print_r(unserialize($content));

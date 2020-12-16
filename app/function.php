<?php
function getSize($filesize) {
	if($filesize >= 1073741824) {
	 $filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';

	} elseif($filesize >= 1048576) {
	 $filesize = round($filesize / 1048576 * 100) / 100 . ' MB';

	} elseif($filesize >= 1024) {
	 $filesize = round($filesize / 1024 * 100) / 100 . ' KB';

	} else {
	 $filesize = $filesize . ' 字节';

	}
	return $filesize;
}
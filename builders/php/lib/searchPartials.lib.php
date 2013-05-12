<?php

// NOT ACTUALLY NEEDED FOR ANYTHING ANYMORE
function searchPartials($m,$data) {

	$tokens = $m->tokenizer->returnTokens();
	foreach ($tokens as $token) {
		if ($token['type'] == '>') {
			$tpl = $m->loadTemplate($token['name']);
			$json = json_decode(file_get_contents('../../source/patterns/'.$token['name'].'/data.json'));
			$data = (object) array_merge((array) $data, (array) $json);
			$data = searchPartials($m,$data);
		}
	}
	return $data;

}
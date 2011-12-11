<?php

/*
 *	PARAMS:
 *		func=
 *			men // gets mentions for twitter account registered
 *			lim // gets limit for account
 *			twt // posts tweet for account
 *
 *			func = men
 *				type=
 *					csv  // returns data in csv for processing ([# of hashes]|[id of mention]|[hash1]|[hashN]|[sender name]|[sender nick]|[tweet_text]
 *
 *			func = twt
 *				cmd  // keeps up with command issued, queries art_tweets for appropriate response
 *				mid  // tracks mentions (art_mention) to respond to appropriate user
 *
 *			func = lim
 *					csv  // returns data in csv for processing ([remaining hits]|[remaining photos])
 *
 */


require("/_sql/sqlopen.php"); //open SQLDB connection

// Use Matt Harris' OAuth library to make the connection
// This lives at: https://github.com/themattharris/tmhOAuth

require_once('tmhOAuth.php');

$twcon = new tmhOAuth(array(
		'consumer_key'    => 'sNUWXmRtjPlWWTMabt8Jlw',
		'consumer_secret' => 'KS0UWPkVOlX0h8HFs76aoCDk9KutZTCIZnSoHb7Y1I',
		'user_token'      => '422366619-ab0Rx4T95d4YhEGcpjCGIVTvlPP2zLqxdh5tkpMU',
		'user_secret'     => '17ZFXHjztgOYE6CwiFMv6epW8dXTN5IVmbjX89yx92c',
	));

$delimit = "|";
$success = "Y";

if (isset($_GET['func'])) {

	if($_GET['func'] == 'men'){
		$men_count = 20; // get only the 20 (most recent) objects (mention) in the json result from twitter

		$men_objs = mysql_query("SELECT men_id, men_reply FROM art_mention") or die (mysql_error()); // grab the info from the last retrieved mention

		$mentions = mysql_fetch_assoc($men_objs);

		$men_id = $mentions['men_id'];

		if($_GET['type'] == 'json'){
			$result = get_mentions($twcon, $men_count, $men_id);

			echo $result;
		}  //close if "json"

		if($_GET['type'] == 'csv'){

			$result = get_mentions($twcon, $men_count, $men_id);

			$decode = json_decode($result, true);

			$mentions = count($decode);

			for($i=$mentions; $i >= 0 ; $i--){

				if (strcmp($decode[$i][id_str],$men_id) > 0) {

					$men_id = $decode[$i][id_str];

					$men_tweet = $decode[$i][text];
					$sender = $decode[$i][user][screen_name];
					$sender_nm = $decode[$i][user][name];

					$h_count = count($decode[$i][entities][hashtags]); //counting the number of hashes

					$id = $i+1;

					echo $id.$delimit.$h_count.$delimit;

					for($j=0; $j<$h_count; $j++){
						$hashes[$j] = strtolower($decode[$i][entities][hashtags][$j][text]);
						//echo $i.' Hash #'.$j.': '.$hashes[$j].'<br>';

						switch ($hashes[$j]) {
						case 'help':
							$tweet_id = get_tweet_id('H');
							$result = post_tweet($tweet_id, $twcon, $sender);
							echo 'H';
							break;
						case 'tweet':
							$tweet_id = get_tweet_id('T');
							$result = post_tweet($tweet_id, $twcon, $sender);
							echo 'T';
							break;
						/*case 'followme':
							$tweet_id = get_tweet_id('F');
							$followed = post_follow($sender);
							$result = post_tweet($tweet_id, $twcon, $sender);
							echo 'F';
							break;*/
						case 'wave':
							echo 'W';
							break;
						case 'love':
							echo 'L';
							break;
						case 'dance':
							echo 'D';
							break;
						case 'blink':
							echo 'B';
							break;
						default:
							echo 'N';
						} // close switch statement
						echo $delimit;
					} // close for-each-hash-tag loop

					echo $success.$delimit.$sender_nm.$delimit.$sender.$delimit.$men_tweet."\n";

					$sql_updatementions = mysql_query("UPDATE art_mention SET men_id = $men_id, men_reply = '$sender' WHERE id = $id") or die (mysql_error());
				} // close if newest mention string comparison check
			} // looping through the mentions
		} // if type = "csv" request
	} // if fund = "men" request

	if($_GET['func'] == 'lim'){

		$result = get_limit($twcon);
		
		if($_GET['type'] == 'csv'){
			
			$decode = json_decode($result, true);
			
			$rem_hits = $decode[remaining_hits];
			$rem_photos =  $decode[photos][remaining_hits];

			echo $rem_hits.$delimit.$rem_photos.$delimit;
		}
		else {
			echo $result;
		}
	}

	if($_GET['func'] == 'twt'){

		if(isset($_GET['cmd'])){

			$twt_cmd = $_GET['cmd'];

			if ($twt_cmd != "") {

				$tweet_id = get_tweet_id($twt_cmd);

				if($_GET['mid'] > 0) {
					$reply_to = get_reply_to($_GET['mid']);
				}
				else {
					$reply_to = 'none';
				}

				$result = post_tweet($tweet_id, $twcon, $reply_to);

				echo $result;
			}
		}
	}
}


function post_tweet($tweet_id, $twcon, $user='none') {

	$tweet_objs = mysql_query("SELECT text FROM art_tweets WHERE id=$tweet_id") or die ('MySQL Error: '.mysql_error());

	$tweets = mysql_fetch_array($tweet_objs);

	$tweet_text = $tweets['text'].' ('.date("H:i:s").')';

	if ($user != 'none') {
		$tweet_text = '@'.$user.', '.$tweet_text;
	}

	$sql_updatetweets = mysql_query("UPDATE art_tweets SET uses = uses + 1 WHERE id=$tweet_id") or die ('MySQL Error: '.mysql_error());

	$twcon->request('POST', $twcon->url('1/statuses/update'),
  	array('status' => $tweet_text));

  	//return $twcon->response['response'];

	return $tweet_text;
}


function get_limit($twcon) {

	$code = $twcon->request('GET', $twcon->url('1/account/rate_limit_status', 'json'));

	if ($code == 200) {
		return $twcon->response['response'];
	}
	else {
		echo 'Service unavailable: '.$code;
	}
}


function get_mentions($twcon, $count=100, $men_id = 1000) {

	$code = $twcon->request('GET', $twcon->url('1/statuses/mentions', 'json'),
		array('since_id' => $men_id, 'count' => $count,'include_entities' => 'true'));

	if ($code == 304) {
		echo 'nothing new';
		//return $twcon->response['response'];
	}
	else {
		return $twcon->response['response'];
		//echo 'Service unavailable: '.$code;
	}
}

function get_reply_to($rid) {
	$reply_to_objs = mysql_query("SELECT men_reply FROM art_mention WHERE id=$rid") or die (mysql_error());
	$replies = mysql_fetch_assoc($reply_to_objs);
	return $replies['men_reply'];
}

function get_tweet_id($cmd) {

	$twt_objs = mysql_query("SELECT id FROM art_tweets WHERE cmd='$cmd'") or die (mysql_error()); // grab the info from the last retrieved mention

	while ($twt_rows = mysql_fetch_assoc($twt_objs)) {
		$tweets[]=$twt_rows['id'];
	}

	$t_count = count($tweets);

	if($t_count > 1){
		$i = rand(0,$t_count-1);
		$tweet_id = $tweets[$i];
	}
	else {
		$tweet_id = $tweets[0];
	}
	return $tweet_id;
}

function post_follow($add_name){
	$twcon->request('POST', $twcon->url('1/friendships/create.format', 'json'),
  		array('screen_name' => $add_name));

  	return $twcon->response['response'];
}

?>
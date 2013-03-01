<?php

// require_once 'EclipseAntiSpamExtensionHooks.php';

// Take credit for your work.
$wgExtensionCredits['parserhook'][] = array(
		'path' => __FILE__,
		// The name of the extension, which will appear on Special:Version.
		'name' => 'Eclipse AntiSpam Extension',
		'description' => 'Eclipse sends a message when you log in. This listens to it and adds the user to the EclipseUsers group',
		'version' => 1,

		// Your name, which will appear on Special:Version.
		'author' => 'Phil Rice',

		// The URL to a wiki page/web page with information about the extension,
		// which will appear on Special:Version.
		'url' => 'https://www.softwarefm.com/Wiki/Manual:EclipseAntiSpamExtension',
);

$extensionObject = new EclipseAntiSpamExtension;

$wgHooks['ArticlePageDataBefore'][] = array($extensionObject, 'onArticlePageDataBefore');

class EclipseAntiSpamExtension {
	public function onArticlePageDataBefore( &$article, &$fields ) {
		$title = $article->getTitle();
		$articleTitle= $title->getText();
		$nameSpace= $title->getSubjectNsText();
		$pUser = self::getUserObj();
		if ($pUser->isLoggedIn() and (($nameSpace == "Artifact:") or ($nameSpace= "Code:") )){
			$pUser->addGroup("Trusted");
		}
		if (($articleTitle== "Command:DontTrustMe") and $pUser->isLoggedIn()){
			$pUser->removeGroup("Trusted");
		}
		$debug="Trusted:".self::ifingroupObj("Trusted");
		$debug =$debug." User: " . self::ifingroupObj("user");
		$debug =$debug . "LoggedIn: " . $pUser->isLoggedIn();
		$debug =$debug." namespace: " . $nameSpace;
		$dbw = wfGetDB( DB_MASTER );
		$dbw->begin();
		$dbw->query("insert into mavenrip.antispam (name, user, groups, debug) values ('".
				$articleTitle . "','".
				$pUser ."','".
				print_r($pUser->getEffectiveGroups(),true)."','" .
				$debug . "')");
		$dbw->commit();
		return true;
	}

	public static function startswith($haystack, $needle) {
		return substr($haystack, 0, strlen($needle)) === $needle;
	}
	public static function mygetallheaders()	{
		foreach($_SERVER as $name => $value) {
			if(substr($name, 0, 5) == 'HTTP_'){
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;
	}

	private static function getUserObj() {
		global $wgUser;
		return $wgUser;
	}

	public static function ifingroupObj ( $grp ) {
		$pUser = self::getUserObj();
		$userGroups = $pUser->getEffectiveGroups();
		return in_array($grp,$userGroups);
	}

}
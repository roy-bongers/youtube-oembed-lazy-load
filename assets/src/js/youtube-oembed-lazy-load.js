window.addEventListener(
	'load',
	function () {
		// search for all YouTube stills.
		var youtube_stills = document.querySelectorAll( 'span[class^="youtube-still"]' );
		if (youtube_stills.length > 0) {
			// allow stills to be clicked.
			for (var i = 0; i < youtube_stills.length; i++) {
				youtube_stills[i].onclick = showYoutubeVideo;
			}

			// enable YouTube API.
			var tag            = document.createElement( 'script' );
			tag.id             = 'youtube-iframe-api';
			tag.src            = 'https://www.youtube.com/iframe_api';
			var firstScriptTag = document.getElementsByTagName( 'script' )[0];
			firstScriptTag.parentNode.insertBefore( tag, firstScriptTag );
		}
	}
);

// array to append all YouTube player objects to.
var players                = [];
var playerUnstartedCounter = [];
// boolean to check if the YouTube API is loaded.
var isYouTubeApiReady = false;

/**
 * Initialize YouTube video, show the video and hide the still.
 */
function showYoutubeVideo() {
	if ( ! isYouTubeApiReady) {
		console.error( 'YouTube API not loaded yet' );
		return;
	}
	var video_id                     = this.getAttribute( 'data-id' );
	players[video_id]                = new YT.Player(
		'youtube-' + video_id,
		{
			width: null,
			height: null,
			videoId: video_id,
			enablejsapi: 1,
			events: {
				'onReady': onPlayerReady,
				'onStateChange': onPlayerStateChange,
			}
		}
	);
	playerUnstartedCounter[video_id] = 0;

	// hide the still.
	document.getElementById( 'youtube-' + this.getAttribute( 'data-id' ) ).parentElement.style.display = 'block';
	// show the youtube iframe.
	this.style.display = 'none';
}

/**
 * Try starting the player muted if it won't autoplay with audio playback.
 */
function onPlayerStateChange(event) {
	// if state is unstarted and we're still at exactly 0 seconds we
	// are probably on mobile and should try to start muted playback.
	if (-1 == event.data && 0 == event.target.getCurrentTime()) {
		if (1 == playerUnstartedCounter[event.target.getVideoData().video_id]) {
			console.log( 'trying muted playback' );
			event.target.mute();
			event.target.playVideo();
		}
		playerUnstartedCounter[event.target.getVideoData().video_id]++;
	}
}

/**
 * Auto play video when the player is loaded.
 */
function onPlayerReady(event) {
	event.target.playVideo();
}

/**
 * Check if the YouTube API is loaded.
 */
window.onYouTubeIframeAPIReady = function() {
	isYouTubeApiReady = true;
}

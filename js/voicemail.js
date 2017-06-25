function __log(e, data) {
    // log.innerHTML += "\n" + e + " " + (data || '');
}

var audio_context;
var recorder;

// Check if we can use audio elements
navigator.mediaDevices.getUserMedia({ audio: true }).then(function(stream) {
    // Yes, we can use the stream!
}).catch(function(err) {
    document.getElementById("audioDisabled").setAttribute("style", "display: block;")
    document.getElementById("voicemail_record").setAttribute("style", "display: none;")
});

document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("recordingInProgressGif").src = variables.imgBaseUrl + "audio-music-equalizer-animated-gifs-animation.gif";
});


/*
// Wrap the getUserMedia function from the different browsers
navigator.getUserMedia = navigator.getUserMedia ||
                         navigator.webkitGetUserMedia ||
                         navigator.mozGetUserMedia;

// Our success callback where we get the media stream object and assign it to a video tag on the page
function onSuccess(mediaObj){
    window.stream = mediaObj;
    var video = document.querySelector("video");
    video.src = window.URL.createObjectURL(mediaObj);
    video.play();
}

// Our error callback where we will handle any issues
function onError(errorObj){
    console.log("There was an error: " + errorObj);
}

// We can select to request audio and video or just one of them
var mediaConstraints = { video: true, audio: true };

// Call our method to request the media object - this will trigger the browser to prompt a request.
navigator.getUserMedia(mediaConstraints, onSuccess, onError);
*/

/*
navigator.getUserMedia (
   // Constraints
   {
      audio: true
   },
   // successCallback
   function(localMediaStream) {
      var audio = document.querySelector("audio");
      audio.src = window.URL.createObjectURL(localMediaStream);
      audio.onloadedmetadata = function(e) {
         // Do something with the video here.
      };
   },

   // errorCallback
   function(err) {
    if (err === PERMISSION_DENIED) {
      // Explain why you need permission and how to update the permission setting
      alert("oopsy, actually we need that!");
    }
   }
);
*/

function startUserMedia(stream) {
    var input = audio_context.createMediaStreamSource(stream);
    __log("Media stream created.");

    // Uncomment if you want the audio to feedback directly
    //input.connect(audio_context.destination);
    //__log("Input connected to audio context destination.");

    recorder = new Recorder(input);
    __log("Recorder initialised.");
}

var recordingInProgress = false;

function pad (value) {
    return value > 9 ? value : "0" + value;
}

var myTimer;

function startTimer() {
    var seconds = 0;

    myTimer = setInterval(function() {
        document.getElementById("seconds").innerHTML = pad(++ seconds % 60);
        document.getElementById("minutes").innerHTML = pad(parseInt(seconds / 60, 10));
    }, 1000);
}

function stopTimer() {
    clearInterval(myTimer);

    document.getElementById("seconds").innerHTML = "00";
    document.getElementById("minutes").innerHTML = "00";
}

function startRecording(button) {
    recordingInProgress = true;

    recorder && recorder.record();

    startTimer();

    button.disabled = true;
    button.title = "Enregistrement en cours";
    button.children[1].innerHTML = "Enregistrement en cours";
    button.setAttribute("class", "inprogress");

    button.nextElementSibling.disabled = false;
    button.nextElementSibling.title = "Stopper l'enregistrement";
    button.nextElementSibling.setAttribute("class", "active");

    document.getElementById("voicemail_recordingInProgress").setAttribute("style", "display: block;");

    __log("Recording...");
}

function stopRecording(button) {
    recordingInProgress = false;

    recorder && recorder.stop();

    stopTimer();

    button.disabled = true;
    button.title = "Stop";
    button.setAttribute("class", "disabled");

    button.previousElementSibling.disabled = true;
    button.previousElementSibling.title = "1. Enregistrer ma question";
    button.previousElementSibling.children[1].innerHTML = "1. Enregistrer ma question";
    button.previousElementSibling.setAttribute("class", "disabled");

    __log("Stopped recording.");

    // Create WAV download link using audio data blob
    createDownloadLink();

    document.getElementById("voicemail_record").setAttribute("style", "display: block;");
    document.getElementById("voicemail_recordingInProgress").setAttribute("style", "display: none;");
    document.getElementById("voicemail_recordedVoicemail").setAttribute("style", "display: block;");
    document.getElementById("voicemail_thanks").setAttribute("style", "display: none;");

    recorder.clear();
}

function createLineBreak() {
    lineBreak = document.createElement("br");
    lineBreak.setAttribute("style", "clear: both;");
    return lineBreak;
}

function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

function validateAndSendVoicemail(event) {
    var isValid = true;

    event.preventDefault();

    var email = document.getElementById("voicemail_email");

    if (email.value !== "" && !validateEmail(email.value)) {
        isValid = false;

        email.setAttribute("style", "border-color: Red;");
    }

    if (grecaptcha.getResponse() === ""){
        isValid = false;

        document.getElementById("voicemail_recaptcha_validation_message").setAttribute("style", "visibility: visible;");
    }

    if (isValid === true) {
        var data = new FormData(document.getElementById("voicemail_form"));

        data.append("question", blob);

        jQuery.ajax({
            type: "POST",
            url: variables.ajaxurl,
            data: data,
            contentType: false,
            processData: false,
            success: function(data) {
                console.log("Successfully sent the data to the server, here is its reply: " + data);
            },
            error: function() {
                console.log("Error while sending the data to the server");
            }
          });
      }
}

function recaptchaCallback(token) {
    // alert("thanks " + document.getElementById("voicemail_name").value);
}

var blob;

function cancelAndRecordANewVoicemail() {
    document.getElementById("voicemail_recordedVoicemail").setAttribute("style", "display: none;");
    startRecording(document.getElementById("voicemail_start_recording"));
}

function createDownloadLink() {
    recorder && recorder.exportWAV(function(newBlob) {
        blob = newBlob;

        var url = URL.createObjectURL(blob);
        var audioTags = document.getElementsByTagName("audio");
        var voicemailAudio;

        for (var i = 0; i < audioTags.length; i++) {
            if (audioTags[i].className == "draftVoicemail") {
                voicemailAudio = audioTags[i];
                break;
            }
        }

        voicemailAudio.controls = true;
        voicemailAudio.src = url;
    });
}

window.onload = function init() {
    try {
        // webkit shim
        window.AudioContext = window.AudioContext || window.webkitAudioContext;
        navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia;
        window.URL = window.URL || window.webkitURL;

        audio_context = new AudioContext;
        __log("Audio context set up.");
        __log("navigator.getUserMedia " + (navigator.getUserMedia ? "available." : "not present!"));
    } catch (e) {
        alert("No web audio support in this browser!");
    }

    navigator.getUserMedia({audio: true}, startUserMedia, function(e) {
      __log("No live audio input: " + e);
    });

    // document.getElementById("voicemail_sendRecord").onclick = validateAndSendVoicemail;
};

function displayEmailValidation(hiddenCheckbox) {
    document.getElementById("voicemail_email_validation_message").setAttribute("style", "visibility: " + ["visible", "hidden"][1-hiddenCheckbox.value] + ";");
}

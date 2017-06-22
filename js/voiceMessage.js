function __log(e, data) {
    log.innerHTML += "\n" + e + " " + (data || '');
}

var audio_context;
var recorder;

// Check if we can use audio elements
navigator.mediaDevices.getUserMedia({ audio: true }).then(function(stream) {
    // Yes, we can use the stream!
}).catch(function(err) {
    document.getElementById("audioDisabled").setAttribute("style", "display: block;")
    document.getElementById("record").setAttribute("style", "display: none;")
});


function setGiphyImageUrl(query, element) {
    request = new XMLHttpRequest;
	request.open("GET", "http://api.giphy.com/v1/gifs/random?api_key=dc6zaTOxFJmzC&limit=1&rating=g&tag=" + query, true);

	request.onload = function() {
		if (request.status >= 200 && request.status < 400) {
            element.src = JSON.parse(request.responseText).data.fixed_height_downsampled_url;

            console.log("ok for Giphy!");
		} else {
			console.log("reached giphy, but API returned an error");
		 }
	};

	request.onerror = function() {
        return null;

		console.log("connection error");
	};

	request.send();
}


document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("disabledMicrophoneGif").onload = function () {
            this.parentNode.parentNode.parentNode.setAttribute("style", "display: block;");
        };

    setGiphyImageUrl("disabled", document.getElementById("disabledMicrophoneGif"));
    document.getElementById("poweredByGiphy").src = variables.imgBaseUrl + "PoweredBy_200px-White_HorizLogo.png";
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

function startRecording(button) {
    recordingInProgress = true;

    recorder && recorder.record();
    button.disabled = true;
    button.nextElementSibling.disabled = false;
    __log("Recording...");

    document.getElementById("recordingInProgress").children[0].children[1].children[0].onload = function () {
            if (recordingInProgress === true) {
                this.parentNode.parentNode.parentNode.setAttribute("style", "display: block;");
            }
        };
    setGiphyImageUrl("recording", document.getElementById("recordingInProgress").children[0].children[1].children[0]);

    document.getElementById("recordingInProgress").children[0].children[2].children[0].src = variables.imgBaseUrl + "PoweredBy_200px-White_HorizLogo.png";
}

function stopRecording(button) {
    recordingInProgress = false;

    recorder && recorder.stop();
    button.disabled = true;
    button.previousElementSibling.disabled = false;
    __log("Stopped recording.");

    // create WAV download link using audio data blob
    createDownloadLink();

    document.getElementById("record").setAttribute("style", "display: block;");
    document.getElementById("voiceMessages").setAttribute("style", "display: block;");
    document.getElementById("recordingslist").setAttribute("style", "display: block;");
    document.getElementById("thanks").setAttribute("style", "display: none;");

    document.getElementById("record").children[0].innerHTML = "Enregistrer une autre question";

    document.getElementById("recordingInProgress").setAttribute("style", "display: none;");

    recorder.clear();
}

function createLineBreak() {
    lineBreak = document.createElement("br");
    lineBreak.setAttribute("style", "clear: both;");
    return lineBreak;
}

function recaptchaCallback(currentElement) {
    alert("yo");
    getNextElement(currentElement).click();
}

function getNextElement(element) {
    // var element = document.getElementById(elementId);
    children = element.parentNode.children;
    len = children.length;
    ind = [].indexOf.call(children, element);
    // Previous element
    // prevElement = children[ind === 0 ? 0 : ind - 1];
    // Next element
    nextElement = children[ind === len ? len : ind + 1];
    return nextElement;
}

function createDownloadLink() {
    recorder && recorder.exportWAV(function(blob) {
        var url = URL.createObjectURL(blob);
        var li = document.createElement("li");
        var au = document.createElement("audio");
        // var hf = document.createElement("a");
        var deleteRecord = document.createElement("button");
        var validateRecord = document.createElement("button");
        var div = document.createElement("div");
        var name = document.createElement("input");
        var email = document.createElement("input");
        var quoted = document.createElement("input");
        var newsletter = document.createElement("input");
        var sendRecord = document.createElement("button");
        var cancel = document.createElement("button");

        au.controls = true;
        au.src = url;

        // hf.href = url;
        // hf.download = new Date().toISOString() + ".wav";
        // hf.innerHTML = hf.download;

        deleteRecord.innerHTML = "Supprimer";
        deleteRecord.setAttribute("class", "buttonAsText");
        deleteRecord.onclick = function() {
            var currentLi = this.parentNode;

            if (currentLi.parentNode.children.length === 1) {
                document.getElementById("voiceMessages").setAttribute("style", "display: none;");
                document.getElementById("record").children[0].innerHTML = "Enregistrer ma question";
            }

            currentLi.remove(this);
        };

        validateRecord.innerHTML = "Ok, je sélectionne cette question";
        validateRecord.onclick = function() {
            picoModal({
                content: "Ah, the pitter patter of tiny feet in huge combat boots.",
                overlayClose: false
            }).show();

            this.parentNode.lastChild.setAttribute("style", "display: block;");
        };

        div.setAttribute("style", "display: none;");

        name.setAttribute("placeholder", "ex : Jean");
        name.setAttribute("onfocus", "this.placeholder=\'\'");
        name.setAttribute("onblur", "this.placeholder=\'ex : Jean\'");
        var labelName = document.createElement("label");
        labelName.htmlFor = "name";
        labelName.appendChild(document.createTextNode("Nom :"));


        email.setAttribute("placeholder", "ex : jean@gmail.com");
        email.setAttribute("onfocus", "this.placeholder=\'\'");
        email.setAttribute("onblur", "this.placeholder=\'ex : jean@gmail.com\'");
        var labelEmail = document.createElement("label");
        labelEmail.htmlFor = "email";
        labelEmail.appendChild(document.createTextNode("Courriel :"));

        quoted.type = "checkbox";
        quoted.name = "quoted";
        quoted.value = "value";
        quoted.id = "quoted";
        var labelQuoted = document.createElement("label");
        labelQuoted.htmlFor = "quoted";
        labelQuoted.appendChild(document.createTextNode("Je ne veux pas être cité (pas de problème, je comprends et respecte ça)"));

        newsletter.type = "checkbox";
        newsletter.name = "newsletter";
        newsletter.value = "value";
        newsletter.id = "newsletter";
        newsletter.checked = true;
        var labelNewsletter = document.createElement("label");
        labelNewsletter.htmlFor = "newsletter";
        labelNewsletter.appendChild(document.createTextNode("J'en profite pour m'inscrire à l'infolettre pour recevoir directement les nouveaux épisodes"));

        /*
        var recaptchaButton = document.createElement("button");
        recaptchaButton.innerHTML = "Recaptcha!";
        recaptchaButton.setAttribute("class", "g-recaptcha");
        recaptchaButton.setAttribute("data-sitekey", "6LckNh4UAAAAAN_QgryUqQH7dUuF0RO8KZmceCX0");
        recaptchaButton.setAttribute("data-callback", "recaptchaCallback(this)");
        */

        var recaptchaDiv = document.createElement("div");
        recaptchaDiv.setAttribute("class", "g-recaptcha");
        recaptchaDiv.setAttribute("data-sitekey", "6LckNh4UAAAAAN_QgryUqQH7dUuF0RO8KZmceCX0");
        recaptchaDiv.setAttribute("data-callback", "recaptchaCallback(this)");
        recaptchaDiv.setAttribute("data-size", "invisible");

        var launchRecaptchaButton = document.createElement("button");
        launchRecaptchaButton.setAttribute("id", "randomId-" + Math.floor((Math.random() * 100) + 1));
        launchRecaptchaButton.innerHTML = "Launch Recaptcha!";
        launchRecaptchaButton.onclick = function() {
            grecaptcha.execute();
        };

        sendRecord.setAttribute("id", "sendRecord");
        sendRecord.innerHTML = "Ok, je soumets cette question";
        sendRecord.onclick = function(recaptchaResponse) {
            var data = new FormData();
            data.append("g-recaptcha-response", recaptchaResponse);
            data.append("question", blob);
            data.append("name", name.value);
            data.append("email", email.value);
            data.append("quoted", quoted.checked);
            data.append("newsletter", newsletter.checked);

            // TODO : remove the use of jQUery
            jQuery.ajax({
                type: "POST",
                url: variables.ajaxurl,
                data: data,
                contentType: false,
                processData: false,
                success: function(data) {
                    console.log("Successfully sent the data to the server, here is its response: " + data);

                    document.getElementById("record").setAttribute("style", "display: none;");
                    document.getElementById("voiceMessages").setAttribute("style", "display: none;");
                    document.getElementById("recordingslist").setAttribute("style", "display: none;");
                    document.getElementById("thanks").setAttribute("style", "display: block;");

                    voiceMessages.parentNode.insertBefore(document.getElementById("thanks"), voiceMessages.nextSibling);
                },
                error: function() {
                  console.log("Error while sending the data to the server");
                }
              });
        };

        cancel.innerHTML = "Annuler";
        cancel.setAttribute("class", "buttonAsText");
        cancel.onclick = function() {
            this.parentNode.setAttribute("style", "display: none;");
        };

        div.appendChild(labelName);
        div.appendChild(name);
        div.appendChild(createLineBreak());

        div.appendChild(labelEmail);
        div.appendChild(email);
        div.appendChild(createLineBreak());

        div.appendChild(quoted);
        div.appendChild(labelQuoted);
        div.appendChild(createLineBreak());

        div.appendChild(newsletter);
        div.appendChild(labelNewsletter);
        div.appendChild(createLineBreak());

        /*
        div.appendChild(recaptchaButton);
        div.appendChild(createLineBreak());
        */

        div.appendChild(launchRecaptchaButton);
        div.appendChild(createLineBreak());

        div.appendChild(recaptchaDiv);
        div.appendChild(createLineBreak());

        div.appendChild(sendRecord);
        div.appendChild(cancel);

        li.appendChild(au);
        // li.appendChild(hf);
        li.appendChild(deleteRecord);
        li.appendChild(validateRecord);
        li.appendChild(div);

        recordingslist.appendChild(li);
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
};

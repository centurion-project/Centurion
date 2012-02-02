/*
	A simple class for displaying file information and progress
	Note: This is a demonstration only and not part of SWFUpload.
	Note: Some have had problems adapting this class in IE7. It may not be suitable for your application.
*/

// Constructor
// file is a SWFUpload file object
// targetID is the HTML element id attribute that the FileProgress HTML structure will be added to.
// Instantiating a new FileProgress object with an existing file will reuse/update the existing DOM elements
function FileProgress(file, targetID) {
	this.fileProgressID = file.id;

	this.opacity = 100;
	this.height = 0;

	/*
	<div class="field-wrapper field-preview-wrapper"> 
	<span class="">
    	<input type="checkbox" checked="checked" value="1" class="field-checkbox" name="filename_delete_logo">       
    	<img src="layouts/backoffice/images/px.png" class="picture" width="50" height="50" />
    </span> 
    <div class="description">2010-06-11 16.38.24 (1).jpg</div> 
    <div class="clear"></div> 
</div> 
*/
	
	this.fileProgressWrapper = document.getElementById(this.fileProgressID);
	if (!this.fileProgressWrapper) {
		this.fileProgressWrapper = document.createElement("div");
		this.fileProgressWrapper.className = "field-wrapper field-preview-wrapper";
		this.fileProgressWrapper.id = this.fileProgressID;

		this.fileProgressElement = document.createElement("div");
		this.fileProgressElement.className = "progressContainer";

		this.progressCancel = document.createElement("a");
		this.progressCancel.className = "progressCancel";
		this.progressCancel.href = "#";
		this.progressCancel.style.visibility = "hidden";
		this.progressCancel.appendChild(document.createTextNode(" "));

		this.progressText = document.createElement("div");
		this.progressText.className = "progressName description";
		this.progressText.appendChild(document.createTextNode(file.name));

		this.progressBar = document.createElement("div");
		this.progressBar.className = "progressBarInProgress";

		this.progressStatus = document.createElement("span");
		this.progressStatus.className = "progressBarStatus";
		this.progressStatus.innerHTML = "&nbsp;";

		this.fileProgressElement.appendChild(this.progressCancel);
		//this.fileProgressElement.appendChild(this.progressText);
		//this.fileProgressElement.appendChild(this.progressStatus);
		this.fileProgressElement.appendChild(this.progressBar);

		this.fileProgressWrapper.appendChild(this.fileProgressElement);
		this.fileProgressWrapper.appendChild(this.progressStatus);
		this.fileProgressWrapper.appendChild(this.progressText);

		document.getElementById(targetID).appendChild(this.fileProgressWrapper);
	} else {
		this.fileProgressElement = $('.progressContainer', this.fileProgressWrapper)[0];
		this.progressCancel = $('.progressCancel', this.fileProgressWrapper)[0];
		this.progressText = $('.progressName', this.fileProgressWrapper)[0];
		this.progressBar = $('.progressBarInProgress', this.fileProgressWrapper)[0];
		this.progressStatus = $('.progressBarStatus', this.fileProgressWrapper)[0];
		this.reset();
	}

	this.height = this.fileProgressWrapper.offsetHeight;
	this.setTimer(null);


}

FileProgress.prototype.setTimer = function (timer) {
	this.fileProgressElement["FP_TIMER"] = timer;
};
FileProgress.prototype.getTimer = function (timer) {
	return this.fileProgressElement["FP_TIMER"] || null;
};

FileProgress.prototype.reset = function () {
	this.fileProgressElement.className = "progressContainer";

	this.progressStatus.innerHTML = "&nbsp;";
	this.progressStatus.className = "progressBarStatus";
	
	this.progressBar.className = "progressBarInProgress";
	this.progressBar.style.width = "0%";
	
	this.appear();	
};

FileProgress.prototype.setProgress = function (percentage) {
	this.fileProgressElement.className = "progressContainer green";
	this.progressBar.className = "progressBarInProgress";
	this.progressBar.style.width = percentage + "%";

	this.appear();	
};
FileProgress.prototype.setComplete = function () {
	this.fileProgressElement.className = "progressContainer blue";
	this.progressBar.className = "progressBarComplete";
	this.progressBar.style.width = "";

	var oSelf = this;
};
FileProgress.prototype.setError = function () {
	this.fileProgressElement.className = "progressContainer red";
	this.progressBar.className = "progressBarError";
	this.progressBar.style.width = "";

	var oSelf = this;
};
FileProgress.prototype.setCancelled = function () {
	this.fileProgressElement.className = "progressContainer";
	this.progressBar.className = "progressBarError";
	this.progressBar.style.width = "";

	var oSelf = this;
};
FileProgress.prototype.setStatus = function (status) {
	this.progressStatus.innerHTML = status;
};

// Show/Hide the cancel button
FileProgress.prototype.toggleCancel = function (show, swfUploadInstance) {
	this.fileProgressElement.childNodes[0].style.visibility = show ? "visible" : "hidden";
	if (swfUploadInstance) {
		var fileID = this.fileProgressID;
		this.fileProgressElement.childNodes[0].onclick = function () {
			swfUploadInstance.cancelUpload(fileID);
			return false;
		};
	}
};

FileProgress.prototype.appear = function () {
	if (this.getTimer() !== null) {
		clearTimeout(this.getTimer());
		this.setTimer(null);
	}
	
	if (this.fileProgressWrapper.filters) {
		try {
			this.fileProgressWrapper.filters.item("DXImageTransform.Microsoft.Alpha").opacity = 100;
		} catch (e) {
			// If it is not set initially, the browser will throw an error.  This will set it if it is not set yet.
			this.fileProgressWrapper.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=100)";
		}
	} else {
		this.fileProgressWrapper.style.opacity = 1;
	}
		
	this.fileProgressWrapper.style.height = "";
	
	this.height = this.fileProgressWrapper.offsetHeight;
	this.opacity = 100;
	this.fileProgressWrapper.style.display = "";
	
};

// Fades out and clips away the FileProgress box.
FileProgress.prototype.disappear = function () {

	var reduceOpacityBy = 15;
	var reduceHeightBy = 4;
	var rate = 30;	// 15 fps

	if (this.opacity > 0) {
		this.opacity -= reduceOpacityBy;
		if (this.opacity < 0) {
			this.opacity = 0;
		}

		if (this.fileProgressWrapper.filters) {
			try {
				this.fileProgressWrapper.filters.item("DXImageTransform.Microsoft.Alpha").opacity = this.opacity;
			} catch (e) {
				// If it is not set initially, the browser will throw an error.  This will set it if it is not set yet.
				this.fileProgressWrapper.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=" + this.opacity + ")";
			}
		} else {
			this.fileProgressWrapper.style.opacity = this.opacity / 100;
		}
	}

	if (this.height > 0) {
		this.height -= reduceHeightBy;
		if (this.height < 0) {
			this.height = 0;
		}

		this.fileProgressWrapper.style.height = this.height + "px";
	}

	if (this.height > 0 || this.opacity > 0) {
		var oSelf = this;
		this.setTimer(setTimeout(function () {
			oSelf.disappear();
		}, rate));
	} else {
		this.fileProgressWrapper.style.display = "none";
		this.setTimer(null);
	}
};
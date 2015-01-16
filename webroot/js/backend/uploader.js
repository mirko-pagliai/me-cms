/*!
 * This file is part of MeCms.
 * @author		Mirko Pagliai <mirko.pagliai@gmail.com>
 * @copyright	Copyright (c) 2015, Mirko Pagliai for Nova Atlantis Ltd
 * @license		http://www.gnu.org/licenses/agpl.txt AGPL License
 * @see			http://www.script-tutorials.com/html5-drag-and-drop-multiple-file-uploader
 */

$(function() {
	var dropArea = $('#uploader .upload-area');
	var icon = $('#uploader .upload-icon');
	var infoArea = $('#uploader .upload-info');
	var progressBar = $('#uploader .progress');
	var error = $('#uploader .upload-error');
	var resultArea = $('#uploader .upload-result');
	var maxSize = 2097152; //2MB

	var list = [];
	var totalSize = 0;
	var totalProgress = 0;

	//Draw progress on the progress bar
	function drawProgress(progress) {
		//Sets the new progress values and updates the progress bar
		var progress = Math.floor(progress * 100);
		$('.progress-bar', progressBar).width(progress + '%').attr('aria-valuenow', progress);
		$('.progress-bar span', progressBar).html(progress + '%');
	}

	//Handles drag over
	function handleDragOver(event) {
		event.stopPropagation();
		event.preventDefault();
	}

	//Handles drag drop
	function handleDrop(event) {
		event.stopPropagation();
		event.preventDefault();

		//Updates icon
		$('.fa', icon).attr('class', 'fa fa-spin fa-spinner');

		//Hides the error message
		if(error.is(':visible'))
			error.hide();

		//Resets the progress bar
		$('.progress-bar', progressBar).width('0').attr('aria-valuenow', '0');
		$('.progress-bar span', progressBar).html('');
		progressBar.show();

		//Shows the info area
		if(infoArea.is(':hidden'))
			infoArea.show();

		processFiles(event.originalEvent.dataTransfer.files);
	}

	//Process files
	function processFiles(filelist) {
		if(!filelist || !filelist.length || list.length)
			return;

		totalSize = 0;
		totalProgress = 0;
		resultArea.empty();

		for(var i = 0; i < filelist.length; i++) {
			list.push(filelist[i]);
			totalSize += filelist[i].size;
		}

		uploadNext();
	}

	//On complete, starts the next file
	function handleComplete(size) {
		totalProgress += size;
		drawProgress(totalProgress / totalSize);
		uploadNext();
	}

	//Updates progress
	function handleProgress(event) {
		var progress = totalProgress + event.loaded;
		drawProgress(progress / totalSize);
	}

	// upload file
	function uploadFile(file, status) {
		// prepare XMLHttpRequest
		var xhr = new XMLHttpRequest();
		xhr.open('POST', window.location.href);

		xhr.onload = function() {
			resultArea.append(this.responseText);
			handleComplete(file.size);
		};

		xhr.onerror = function() {
			resultArea.html(this.responseText);
			handleComplete(file.size);
		};

		xhr.upload.onprogress = function(event) {
			handleProgress(event);
		}

		xhr.upload.onloadstart = function(event) {}

		//Prepares FormData
		var formData = new FormData();  
		formData.append('file', file); 
		xhr.send(formData);
	}

	//Uploads the next file
	function uploadNext() {
		if(list.length) {
			var nextFile = list.shift();
			//If the file exceeds the maximum size
			if(nextFile.size >= maxSize) {
				//Shows the error message
				$('strong', error).html(nextFile.name);
				error.show();
				
				handleComplete(nextFile.size);
			}
			else
				uploadFile(nextFile, status);
		}
		else {
			//Updates icon
			$('.fa', icon).attr('class', 'fa fa-cloud-upload');
			//Hides the progress bar
			progressBar.hide();
		}
	}

	//Inits handlers
	dropArea.on({drop: handleDrop, dragover: handleDragOver});
});
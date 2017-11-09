class UploaderComponent {

	constructor(root) {
		this.root = root;
		this.input = this.root.querySelector('input[type=file]');
		this.inputGroup = this.root.querySelector('.input-group');
		this.dropZone = this.root.querySelector('.drop-zone');
		this.previewer = this.root.querySelector('.previewer');
		this.progressBar = this.root.querySelector('.progress');
		this.progress = this.root.querySelector('.progress-bar');
		this.initialValue = this.previewer.innerHTML;

		if (!this.dropZone) {
			this.inputGroup.style.display = 'block';
		}

		this._binds();
		this._addEventListeners();
	}

	_binds() {
		this._onChange = this._onChange.bind(this);
		this._errorHandler = this._errorHandler.bind(this);
		this._updateProgress = this._updateProgress.bind(this);
		this._onLoadStarts = this._onLoadStarts.bind(this);
		this._onClick = this._onClick.bind(this);
	}

	_addEventListeners() {
		this.input.addEventListener('change', this._onChange, false);

		if (this.dropZone) {
			this.dropZone.addEventListener('click', this._onClick, false);
			this.dropZone.addEventListener('drop', this._onChange, false);
			this.dropZone.addEventListener('dragover', this._onDragOver, false);
			this.dropZone.addEventListener('dragend', this._onDragEnd, false);
		}
	}

	_onChange(evt) {
		evt.stopPropagation();
		evt.preventDefault();

		if (!window.File && !window.FileReader && !window.FileList && !window.Blob) {
			return;
		}

		let files = [];

		if (evt instanceof DragEvent) {
			files = evt.dataTransfer.files;
		} else {
			files = evt.target.files;
		}

		if (!files.length) {
			this.previewer.innerHTML = this.initialValue;

			if (!this.initialValue) {
				this.previewer.style.display = 'none';
			}

			return;
		}

		this.progress.style.width = '0%';
		this.progress.textContent = '0%';

		const reader = new FileReader();

		reader.onload = this._onLoad(files[0]);
		reader.onerror = this._errorHandler;
		reader.onprogress = this._updateProgress;
		reader.onloadstart = this._onLoadStarts;

		reader.readAsDataURL(files[0]);
	}

	_onLoadStarts(evt) {
		this.previewer.innerHTML = '';
		this.previewer.style.display = 'none';
		this.progressBar.classList.add('loading');
	}

	_onLoad(file) {
		return e => {
			this.previewer.style.display = 'block';

			if (file.type.match('image.*')) {
				this.previewer.innerHTML = [
					'<img class="thumb" src="', e.target.result, '" title="', escape(file.name), '"/>', '<p class="sr-only">', file.name, '</p>'
				].join('');
			} else {
				this.previewer.innerHTML = [
					'<p>', '<span class="material-icons" title="', escape(file.name), '">insert_drive_file</span> ', file.name, '</p>'
				].join('');
			}


			this.progress.style.width = '100%';
			this.progress.textContent = '100%';
			setTimeout(_ => this.progressBar.classList.remove('loading'), 200);
		};
	}

	_updateProgress(evt) {
		if (evt.lengthComputable) {
			const percentLoaded = Math.round((evt.loaded / evt.total) * 100);

			if (percentLoaded < 100) {
				this.progress.style.width = percentLoaded + '%';
				this.progress.textContent = percentLoaded + '%';
			}
		}
	}

	_errorHandler(evt) {
		switch (evt.target.error.code) {
			case evt.target.error.NOT_FOUND_ERR:
				alert('File Not Found!');
				break;
			case evt.target.error.NOT_READABLE_ERR:
				alert('File is not readable');
				break;
			case evt.target.error.ABORT_ERR:
				break;
			default:
				alert('An error occurred reading this file.');
		};
	}

	_onClick(evt) {
		this.input.click();
	}

	_onDragOver(evt) {
		evt.stopPropagation();
		evt.preventDefault();
		evt.dataTransfer.dropEffect = 'copy';
		evt.target.classList.add('dragging-over');
		evt.target.classList.add('border-primary');
	}

	_onDragOver(evt) {
		evt.stopPropagation();
		evt.preventDefault();
		evt.target.classList.remove('dragging-over');
		evt.target.classList.remove('border-primary');
	}

	static loadComponents() {
		document
			.querySelectorAll('.component__uploader')
			.forEach(component => new UploaderComponent(component));
	}

}

/**
 * Created by zhwei on 15/10/28.
 */

// 支持的上传类型
var FILE_TYPES = {
	'doc': {
		title: '文档',
		ext: 'pdf,xls,xlsx,doc,docx',
		upUrl: '/admin/upload-token/document',
		downUrl: null
	},
	'image': {
		title: '图片',
		ext: 'jpeg,jpg,gif,png',
		upUrl: '/admin/upload-token/image',
		downUrl: '/admin/image-download-url?width=100&height=100'
	}
};

// 存放当前页面中所有的uploader实例
var uploaderList = [];

// 存放当前页面中所有的上传文件数据, {'images': {'户型图': [...], ...}, ... }
var fileList = {};
var fileLinks = {};

$(document).ready(function () {
	$('.qn-upload-links').each(function () {
		var text = $(this).text().trim();
		if (text) {
			$.each(JSON.parse(text), function ($name, $value) {
				fileLinks[$name] = $value;
			});
		}
	});

	$('.qn-upload').each(function () {
		$this = $(this);
		$this.css('display', 'none');
		if (this.tagName == 'INPUT') {
			var up = new Uploader($this);
			up.render();
			uploaderList.push(up);
		} else {
			$this.find('span.qn-upload-part').each(function () {
				var up = new Uploader($(this));
				up.render();
				uploaderList.push(up);
			});
		}
	});
});

var PREFIX_UPLOADER_BLOCK = 'qn-upload-block-';
var PREFIX_UPLOADER_PICKER = 'qn-upload-picker-';

function Uploader($trigger, submitName) {
	// <span data-name="images" data-type="image">
	this.name = $trigger.data('name');

	// 上层的的div
	this.parentTrigger = $trigger.closest('.qn-upload');
	this.submitName = this.parentTrigger.data('name');

	// 各种变量
	this.staus = this.parentTrigger.data('status');
	this.staus = this.staus ? this.staus : 'modify'; // 默认modify
	this.type = $trigger.data('type'); // 具体的上传类型由子标签上的`data-type`决定
	this.typeConfig = FILE_TYPES[this.type];
	this.browseBtnId = PREFIX_UPLOADER_PICKER + this.name;
	this.containerId = PREFIX_UPLOADER_BLOCK + this.name;
	this.isRequired = this.parentTrigger.data('required');

	// 创建 or 拿到填充数据的input
	this.input = this.parentTrigger.siblings('[name=' + this.submitName + ']');
	this.input.css('display', 'none');

	// 准备fileList里的存放位置
	if (typeof fileList[this.submitName] == 'undefined') {
		fileList[this.submitName] = {};
	}
	fileList[this.submitName][this.name] = [];

	// 方便调用
	var self = this;

	// 写入fileList的函数
	this.pushFileKey = function (key) {
		if (fileList[this.submitName][this.name].indexOf(key) == -1) {
			fileList[this.submitName][this.name].push(key);
		}
	};

	this.isModify = function () {
		return ['modify', 'create'].indexOf(this.staus) > -1;
	};

	// 生成上传控件
	this.render = function () {
		this.parentTrigger.before($('<div/>', {
			'id': this.containerId,
			'class': 'panel panel-default',
			'style': 'margin-top: 15px;',
			'html': [
				$('<div/>', {
					'class': 'panel-heading',
					'html': [
						'<span style="margin-right: 1em;">' + (this.isRequired ? '* ' : '') + this.name + '</span>',
						this.isModify() ? '<span id="' + this.browseBtnId + '" class="btn btn-primary btn-sm">添加' + this.typeConfig.title + '（可多选）</span>' : ''
					]
				}),
				$('<div/>', {'class': 'panel-body'})
			]
		}));

		this.previewer = $('#' + this.containerId).find('.panel-body');
		this.initQiNiuUploader();

		var val = this.getValue();
		if (val) {
			fileList[this.submitName] = val;
			this.showUploadFiles();
		}

		if (this.isModify() && this.isRequired) {
			this.bindRequired();
		}
	};

	this.getValue = function () {
		var str = $.trim(this.isModify() ? this.input.val() : this.input.text());
		return str ? JSON.parse(str) : null;
	};

	// 填充form
	this.fillForm = function () {
		this.input.val(JSON.stringify(fileList[this.submitName]));
	};

	this.imagePreview = function (file) {
		// hasUpload = (typeof hasUpload !== 'undefined') ? b : false; // 默认参数用来生成未上传图片
		var $img = $('<img/>', {
			'id': file.id,
			'class': 'img-thumbnail',
			'alt': file.name + ' 上传中'
		});

		//@todo 这部分还有bug，一个页面上有个多个控件的时候有的缩略图不显示。
		if (file.size < 2000000) { //如果文件过大，手机压缩时间会很长。
			//缩略图 From http://bennadel.github.io/Plupload-Image-Preview/
			var preloader = new mOxie.Image();
			preloader.onload = function () {
				preloader.downsize(100, 100);
				var imgUrl = preloader.getAsDataURL();
				$img.prop("src", imgUrl);
				$img.after('<p><small><a href="' + imgUrl + '" target="_blank">' + file.name + '</a></small></p>');
			};
			preloader.load(file.getSource());
		} else {
			$img.prop("alt", "上传中。文件太大，无缩略图。");
		}

		return $img
	};

	// 已经存在的文件的预览
	this.showUploadFiles = function () {
		var files = fileList[this.submitName][this.name];
		$.each(files ? files : [], function (idx, key) {
			var $div = $('<div/>', {
				'class': 'file-block img-thumbnail text-center',
				'style': 'margin-right: 10px;',
				'data-key': key,
				'html': self.isModify() ? '<p><span class="btn btn-xs btn-danger file-delete">删除</span></p>' : ''
			});
			self.previewer.append($div);

			if (self.type == 'image') {
				$div.prepend('<p><a href="' + fileLinks[key].url + '" target="_blank"><img src="' + fileLinks[key].small + '"></a></p>');
			} else {
				$div.prepend('<p><a href="' + fileLinks[key].url + '" target="_blank">' + fileLinks[key].title + '</a></p>');
			}
		});
		this.bindDelete();
	};

	this.previewFile = function (file) {
		var $div = $('<div/>', {
			'id': 'block-' + file.id,
			'class': 'file-block img-thumbnail text-center',
			'style': 'margin-right: 10px;',
			'html': '<p><span class="btn btn-xs btn-warning file-delete">0%</span></p>'
		});

		if (this.type == 'image') {
			$div.prepend(this.imagePreview(file))
		} else {
			$div.prepend('<small>' + file.name + '</small>');
		}

		this.previewer.append($div);
		this.bindDelete();
	};

	this.bindRequired = function () {
		this.parentTrigger.closest('form').submit(function (event) {
			if (fileList[self.submitName][self.name].length == 0) {
				event.preventDefault();
				alert(self.name + '不能为空');
			}
		})
	};

	// 删除按钮的监听事件
	this.bindDelete = function () {
		$('.file-delete').on('click', function () {
			var $block = $(this).closest('.file-block');
			var key = $block.data('key');
			if (key != undefined) {
				var idx = fileList[self.submitName][self.name].indexOf(key);
				if (idx > -1) {
					fileList[self.submitName][self.name].splice(idx, 1)
				}
			}
			$block.fadeOut('fast');
			self.fillForm()
		});
	};

	// 初始化七牛的 uploader & 真正的上传过程
	this.initQiNiuUploader = function () {
		var qn = new QiniuJsSDK();
		this.qnUploader = qn.uploader({
			container: self.containerId,        //上传区域DOM ID，默认是browser_button的父元素，
			browse_button: self.browseBtnId,       //上传选择的点选按钮，**必需**
			multi_selection: true, //note: 支持多选的时候，会导致无法使用摄像头直接拍.
			runtimes: 'html5,flash,html4',    //上传模式,依次退化
			filters: {
				mime_types: [{
					title: this.typeConfig.title,
					extensions: this.typeConfig.ext
				}]
			},
			uptoken_url: this.typeConfig.upUrl,            //Ajax请求upToken的Url，**强烈建议设置**（服务端提供）
			downtoken_url: this.typeConfig.downUrl,
			save_key: true,   // 默认 false。若在服务端生成uptoken的上传策略中指定了 `sava_key`，则开启，SDK在前端将不对key进行任何处理
			domain: 'http://',   //bucket 域名，下载资源时用到，**有downtoken_url，这个就不需要了。**
			max_file_size: '100mb',   //最大文件体积限制
			chunk_size: '3mb',  //分块上传时，每片的体积 (这个值如果大于4M，会被qiniu js sdk reset)
			flash_swf_url: '/bower/plupload/js/Moxie.swf',  //引入flash,相对路径
			max_retries: 3,      //上传失败最大重试次数
			dragdrop: false,     //开启可拖曳上传
			auto_start: true,                 //选择文件后自动上传，若关闭需要自己绑定事件触发上传,
			init: {
				'FilesAdded': function (up, files) {
					plupload.each(files, function (file) {
						self.previewFile(file);
					});
				},
				'UploadProgress': function (up, file) {
					var $imgDiv = $('#block-' + file.id).find('.btn');
					if ($imgDiv.length != 0) {
						if (file.percent == 100) {
							$imgDiv.removeClass('btn-warning');
							$imgDiv.addClass('btn-danger');
							$imgDiv.text('删除');
						} else {
							$imgDiv.text(file.percent + '%');
						}
					}
				},
				'FileUploaded': function (up, file, info) {
					// 每个文件上传成功后,处理相关的事情
					// 其中 info 是文件上传成功后，服务端返回的json，形式如
					// {
					//    "hash": "Fh8xVqod2MQ1mocfI4S4KpRL6D98",
					//    "key": "nnnnn.png",
					//    "url":"http://xx.glb.qiniucdn.com/nnnnnn.png?e=xxx"
					//  }
					// 参考http://developer.qiniu.com/docs/v6/api/overview/up/response/simple-response.html
					var $block = $('#block-' + file.id);
					var _info = $.parseJSON(info);
					$block.data('key', _info.key);
					$block.find('img').prop('src', _info.url);
					self.pushFileKey(_info.key);
					self.fillForm();
				},
				'Error': function (up, err, errTip) {
					//上传出错时,处理相关的事情
					var $block = $('#block-' + err.file.id);
					$block.prepend('<p style="color: red;"><small>文件上传失败<br>' + errTip + '</small></p>');
				},
				'UploadComplete': function () {
					//队列文件处理完毕后,处理相关的事情
				}
			}
		});
	}
}
document.addEventListener('DOMContentLoaded', () => {
    const imageInput = document.getElementById('imageInput');
    const uploadSection = document.getElementById('upload-section');
    const loadingSection = document.getElementById('loading-section');
    const editorSection = document.getElementById('editor-section');
    const uploadError = document.getElementById('uploadError');
    const originalPreview = document.getElementById('originalPreview');
    const resultPreview = document.getElementById('resultPreview');
    const downloadBtn = document.getElementById('downloadBtn');
    const resetBtn = document.getElementById('resetBtn');
    const customColorPicker = document.getElementById('customColorPicker');
    const dropzone = document.getElementById('dropzone');

    let currentFileId = null;
    let currentProcessedFileName = null;
    let selectedBg = 'transparent';

    if (!imageInput) {
        console.error('imageInput element not found!');
        return;
    }

    // 文件选择框变更事件
    imageInput.addEventListener('change', (e) => {
        if (e.target.files && e.target.files.length > 0) {
            handleFile(e.target.files[0]);
        }
    });

    // 拖拽悬停与放置事件支持
    if (dropzone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.style.borderColor = '#2563eb';
                dropzone.style.background = '#f8fafc';
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.style.borderColor = '#cbd5e1';
                dropzone.style.background = '#ffffff';
            }, false);
        });

        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files.length > 0) {
                handleFile(files[0]);
            }
        });
    }

    // 处理文件上传核心逻辑
    function handleFile(file) {
        uploadError.style.display = 'none';
        uploadError.textContent = '';

        const formData = new FormData();
        formData.append('image', file);
        
        // 安全携带 CSRF 令牌
        const csrfToken = typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '';
        formData.append('csrf_token', csrfToken);

        // 本地预览原图
        const reader = new FileReader();
        reader.onload = (e) => {
            originalPreview.src = e.target.result;
        };
        reader.readAsDataURL(file);

        // 切换视图
        uploadSection.classList.add('d-none');
        loadingSection.classList.remove('d-none');

        // 发送异步上传请求
        fetch('upload.php', {
            method: 'POST',
            body: formData
        })
        .then(async res => {
            const text = await res.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error('Server response is not valid JSON: ' + text.substring(0, 100));
            }
        })
        .then(data => {
            if (!data.success) throw new Error(data.error || 'Upload failed.');
            currentFileId = data.file_id;
            return processBackground(currentFileId);
        })
        .catch(err => {
            loadingSection.classList.add('d-none');
            uploadSection.classList.remove('d-none');
            uploadError.textContent = err.message;
            uploadError.style.display = 'block';
        });
    }

    // 调用 AI 抠图处理
    function processBackground(fileId) {
        const csrfToken = typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : '';
        
        fetch('process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                file_id: fileId, 
                csrf_token: csrfToken 
            })
        })
        .then(async res => {
            const text = await res.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error('Server response is not valid JSON: ' + text.substring(0, 100));
            }
        })
        .then(data => {
            if (!data.success) throw new Error(data.error || 'AI processing failed.');
            currentProcessedFileName = data.processed_file_id;
            resultPreview.src = data.preview_url;
            updateDownloadLink();

            loadingSection.classList.add('d-none');
            editorSection.classList.remove('d-none');
        })
        .catch(err => {
            loadingSection.classList.add('d-none');
            uploadSection.classList.remove('d-none');
            uploadError.textContent = err.message;
            uploadError.style.display = 'block';
        });
    }

    // 背景颜色切换按钮
    const bgButtons = document.querySelectorAll('.bg-options .btn');
    bgButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            bgButtons.forEach(b => b.classList.remove('active'));
            e.target.classList.add('active');
            selectedBg = e.target.getAttribute('data-bg');
            
            const container = document.getElementById('resultContainer');
            if (selectedBg === 'transparent') {
                container.classList.add('checkerboard');
                container.style.backgroundColor = '';
            } else {
                container.classList.remove('checkerboard');
                container.style.backgroundColor = selectedBg;
            }
            updateDownloadLink();
        });
    });

    // 自定义颜色选择器
    if (customColorPicker) {
        customColorPicker.addEventListener('input', (e) => {
            bgButtons.forEach(b => b.classList.remove('active'));
            selectedBg = e.target.value;
            const container = document.getElementById('resultContainer');
            container.classList.remove('checkerboard');
            container.style.backgroundColor = selectedBg;
            updateDownloadLink();
        });
    }

    function updateDownloadLink() {
        if (!currentProcessedFileName) return;
        downloadBtn.href = `download.php?file=${encodeURIComponent(currentProcessedFileName)}&bg=${encodeURIComponent(selectedBg)}`;
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            editorSection.classList.add('d-none');
            uploadSection.classList.remove('d-none');
            imageInput.value = '';
            currentFileId = null;
            currentProcessedFileName = null;
            originalPreview.src = '';
            resultPreview.src = '';
        });
    }
});
<?php
require_once __DIR__ . '/config/bootstrap.php';
$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RemoveBG — AI Background Removal System</title>
    <!-- FontAwesome 图标库 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- 自定义原生 CSS（无需依赖外部 Bootstrap，强制左右并排与居中） -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- 顶部导航栏 -->
    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fa-solid fa-wand-magic-sparkles me-2" style="color: #38bdf8;"></i>RemoveBG
            </a>
        </div>
    </nav>

    <!-- 页面核心区域 -->
    <main class="container">
        <!-- 标题区 -->
        <div class="text-center mb-5">
            <h1 class="display-5">Remove Image Backgrounds in Seconds</h1>
            <p style="color: #64748b; font-size: 1.1rem; margin-top: 0.5rem;">
                Upload an image, let AI remove the background, and download a clean transparent image in just a few clicks.
            </p>
        </div>

        <!-- 1. 上传区域 (Upload Section) -->
        <div id="upload-section">
            <label for="imageInput" class="upload-dropzone" id="dropzone" style="display: block; cursor: pointer;">
                <div style="margin-bottom: 1.5rem;">
                    <i class="fa-solid fa-cloud-arrow-up fa-4x" style="color: #2563eb; font-size: 3.5rem;"></i>
                </div>
                <h3 style="margin: 0 0 0.5rem 0; font-weight: 700;">Drag & Drop your image here</h3>
                <p style="color: #64748b; margin-bottom: 1rem;">or click to browse from your computer</p>
                <p style="color: #94a3b8; font-size: 0.875rem; margin-bottom: 1.5rem;">Supports JPG, PNG, WebP (Max 10MB)</p>
                
                <!-- 原生文件输入框，通过 label 完美触发，绝不报错 -->
                <input type="file" id="imageInput" accept="image/jpeg,image/png,image/webp" style="display: none;">
                
                <div>
                    <span class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1rem; display: inline-block;">
                        <i class="fa-solid fa-folder-open me-2"></i> Upload Image
                    </span>
                </div>
            </label>
            <!-- 错误提示 -->
            <div id="uploadError" style="display: none; background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 1rem; border-radius: 0.5rem; margin-top: 1rem; text-align: center;"></div>
        </div>

        <!-- 2. 加载动画区域 (Loading Section) -->
        <div class="d-none text-center" id="loading-section" style="padding: 5rem 0;">
            <div style="display: inline-block; width: 3.5rem; height: 3.5rem; border: 4px solid #e2e8f0; border-top-color: #2563eb; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 1.5rem;"></div>
            <h4 style="margin: 0 0 0.5rem 0; font-weight: 700;">AI is removing your background...</h4>
            <p style="color: #64748b; margin: 0;">This will only take a moment.</p>
        </div>

        <!-- 3. 图片编辑器与对比预览区域 (Editor Section — 强制左右 50% 并排) -->
        <div class="d-none" id="editor-section">
            <!-- 预览区顶部工具栏 -->
            <div class="editor-header">
                <h3>Preview & Customize</h3>
                <button class="btn btn-outline-secondary" id="resetBtn">
                    <i class="fa-solid fa-rotate-left me-1"></i> Process Another Image
                </button>
            </div>

            <!-- 左右 50% 并排布局结构 -->
            <div class="editor-grid">
                <!-- 左卡片：原图预览 -->
                <div class="editor-card-wrapper">
                    <div class="card">
                        <h5 class="card-title">Original Image</h5>
                        <div class="image-preview-container">
                            <img id="originalPreview" class="max-height-400" alt="Original">
                        </div>
                    </div>
                </div>

                <!-- 右卡片：抠图结果与颜色选择 -->
                <div class="editor-card-wrapper">
                    <div class="card">
                        <h5 class="card-title">Background Removed</h5>
                        <div class="image-preview-container checkerboard" id="resultContainer">
                            <img id="resultPreview" class="max-height-400" alt="Processed">
                        </div>
                        
                        <!-- 底部背景切换工具栏 -->
                        <div class="editor-controls">
                            <div class="btn-group bg-options">
                                <button type="button" class="btn active" data-bg="transparent">Transparent</button>
                                <button type="button" class="btn" data-bg="white">White</button>
                                <button type="button" class="btn" data-bg="black">Black</button>
                                <input type="color" id="customColorPicker" value="#3b82f6" title="Choose custom color">
                            </div>
                            <a id="downloadBtn" href="#" class="btn btn-success">
                                <i class="fa-solid fa-download me-1"></i> Download PNG
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Spin 动画内联样式补充 -->
    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .d-none {
            display: none !important;
        }
    </style>

    <!-- 全局 CSRF 令牌及 JavaScript 脚本 -->
    <script>
        const CSRF_TOKEN = '<?= $csrfToken ?>';
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVG Converter</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #0a0a0a;
            color: #e0e0e0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: #141414;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            padding: 40px;
            width: 100%;
            max-width: 480px;
        }
        h1 {
            font-size: 1.5rem;
            margin-bottom: 8px;
            color: #fff;
        }
        p.sub {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }
        .upload-area {
            border: 2px dashed #2a2a2a;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s;
            margin-bottom: 20px;
        }
        .upload-area:hover { border-color: #444; }
        .upload-area span { color: #888; font-size: 0.95rem; }
        .upload-area .icon { font-size: 2rem; margin-bottom: 10px; display: block; }
        input[type="file"] { display: none; }
        #filename {
            color: #4ade80;
            font-size: 0.85rem;
            margin-bottom: 16px;
            min-height: 20px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #fff;
            color: #000;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        button:hover { opacity: 0.85; }
        .result {
            margin-top: 24px;
            padding: 16px;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        .result.success { background: #0a2a0a; border: 1px solid #1a4a1a; color: #4ade80; }
        .result.error { background: #2a0a0a; border: 1px solid #4a1a1a; color: #f87171; }
    </style>
</head>
<body>
    <div class="container">
        <h1>SVG to PNG Converter</h1>
        <p class="sub">Upload an SVG file and we'll convert it to PNG for you.</p>

        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <div class="upload-area" onclick="document.getElementById('file').click()">
                <span class="icon">&#128196;</span>
                <span>Click to select an SVG file</span>
            </div>
            <input type="file" id="file" name="svg" accept=".svg" onchange="showName(this)">
            <div id="filename"></div>
            <button type="submit">Convert to PNG</button>
        </form>

        <?php if (isset($_GET['success'])): ?>
            <div class="result success">Converted successfully. Output saved.</div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="result error"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
    </div>

    <script>
        function showName(input) {
            const name = input.files[0]?.name || '';
            document.getElementById('filename').textContent = name;
        }
    </script>
</body>
</html>

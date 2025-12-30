<?php include 'database.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive Map - User View</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .controls {
            background: #f8f9fa;
            padding: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            border-bottom: 2px solid #e9ecef;
        }

        .map-container.fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 9999;
            border-radius: 0;
        }

        .fullscreen-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 100;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }

        .fullscreen-btn:hover {
            background: white;
            transform: scale(1.1);
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .zoom-info {
            margin-left: auto;
            color: #666;
            font-weight: 600;
        }

        .map-container {
            position: relative;
            width: 100%;
            height: 700px;
            overflow: hidden;
            background: #f0f0f0;
            cursor: grab;
        }

        .map-container:active {
            cursor: grabbing;
        }

        .map-wrapper {
            position: absolute;
            transform-origin: 0 0;
            transition: transform 0.1s ease-out;
        }

        .map-image {
            display: block;
            max-width: 100%;
            height: auto;
            user-select: none;
            -webkit-user-drag: none;
        }

        .point {
            position: absolute;
            width: 40px;
            height: 40px;
            cursor: pointer;
            transform: translate(-50%, -100%);
            transition: all 0.3s;
            z-index: 10;
        }

        .point:hover {
            transform: translate(-50%, -100%) scale(1.2);
            z-index: 20;
        }

        .point-icon {
            width: 100%;
            height: 100%;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            border: 3px solid white;
        }

        .point-pulse {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7);
            }
            70% {
                box-shadow: 0 0 0 20px rgba(255, 255, 255, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
            }
        }

        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            z-index: 1000;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            animation: popupIn 0.3s ease-out;
        }

        @keyframes popupIn {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        .popup.active {
            display: block;
        }

        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 999;
        }

        .popup-overlay.active {
            display: block;
        }

        .popup-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .popup-header h3 {
            font-size: 1.5em;
            margin: 0;
        }

        .close-popup {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            font-size: 24px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .close-popup:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }

        .popup-body {
            padding: 25px;
        }

        .popup-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .popup-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 15px;
        }

        .popup-link {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 600;
        }

        .popup-link:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .loading {
            text-align: center;
            padding: 50px;
            font-size: 18px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗺️ Interactive Map</h1>
            <p>Explore the map by zooming and clicking on points</p>
        </div>

        <div class="controls">
            <button class="btn btn-primary" onclick="zoomIn()">
                <span>🔍+</span> Zoom In
            </button>
            <button class="btn btn-primary" onclick="zoomOut()">
                <span>🔍-</span> Zoom Out
            </button>
            <button class="btn btn-primary" onclick="resetView()">
                <span>🔄</span> Reset View
            </button>
            <div class="zoom-info">
                Zoom: <span id="zoomLevel">100%</span>
            </div>
        </div>

        <div class="map-container" id="mapContainer">
            <div class="map-wrapper" id="mapWrapper">
                <img src="" alt="Map" class="map-image" id="mapImage">
            </div>
        </div>
    </div>

    <div class="popup-overlay" id="popupOverlay" onclick="closePopup()"></div>
    <div class="popup" id="popup">
        <div class="popup-header">
            <h3 id="popupTitle"></h3>
            <button class="close-popup" onclick="closePopup()">×</button>
        </div>
        <div class="popup-body">
            <img src="" alt="" class="popup-image" id="popupImage" style="display: none;">
            <div class="popup-description" id="popupDescription"></div>
            <a href="#" class="popup-link" id="popupLink" target="_blank" style="display: none;">Visit Link</a>
        </div>
    </div>

    <script>
        let scale = 1;
        let translateX = 0;
        let translateY = 0;
        let isDragging = false;
        let startX, startY;
        let mapData = null;
        let points = [];

        const mapContainer = document.getElementById('mapContainer');
        const mapWrapper = document.getElementById('mapWrapper');
        const mapImage = document.getElementById('mapImage');

        // Load map and points
        async function loadMap() {
            try {
                const response = await fetch('database.php?action=get_map');
                const result = await response.json();
                
                if (result.success) {
                    mapData = result.data;
                    mapImage.src = result.data.map_image;
                    mapImage.onload = () => {
                        loadPoints();
                    };
                } else {
                    mapContainer.innerHTML = '<div class="loading">No map available</div>';
                }
            } catch (error) {
                console.error('Error loading map:', error);
                mapContainer.innerHTML = '<div class="loading">Error loading map</div>';
            }
        }

        async function loadPoints() {
            try {
                const response = await fetch('database.php?action=get_points');
                const result = await response.json();
                
                if (result.success) {
                    points = result.data;
                    renderPoints();
                }
            } catch (error) {
                console.error('Error loading points:', error);
            }
        }

        function renderPoints() {
            // Remove existing points
            document.querySelectorAll('.point').forEach(p => p.remove());
            
            points.forEach(point => {
                const pointEl = document.createElement('div');
                pointEl.className = 'point';
                pointEl.style.left = point.x_coordinate + '%';
                pointEl.style.top = point.y_coordinate + '%';
                pointEl.onclick = () => showPopup(point);
                
                pointEl.innerHTML = `
                    <div class="point-pulse" style="background: ${point.icon_color};"></div>
                    <div class="point-icon" style="background: ${point.icon_color};"></div>
                `;
                
                mapWrapper.appendChild(pointEl);
            });
        }

        function showPopup(point) {
            document.getElementById('popupTitle').textContent = point.title;
            document.getElementById('popupDescription').textContent = point.description || 'No description available';
            
            const popupImage = document.getElementById('popupImage');
            if (point.image) {
                popupImage.src = point.image;
                popupImage.style.display = 'block';
            } else {
                popupImage.style.display = 'none';
            }
            
            const popupLink = document.getElementById('popupLink');
            if (point.link) {
                popupLink.href = point.link;
                popupLink.style.display = 'inline-block';
            } else {
                popupLink.style.display = 'none';
            }
            
            document.getElementById('popup').classList.add('active');
            document.getElementById('popupOverlay').classList.add('active');
        }

        function closePopup() {
            document.getElementById('popup').classList.remove('active');
            document.getElementById('popupOverlay').classList.remove('active');
        }

        function updateTransform() {
            mapWrapper.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`;
            document.getElementById('zoomLevel').textContent = Math.round(scale * 100) + '%';
        }

        function zoomIn() {
            scale = Math.min(scale * 1.2, 5);
            updateTransform();
        }

        function zoomOut() {
            scale = Math.max(scale / 1.2, 0.5);
            updateTransform();
        }

        function resetView() {
            scale = 1;
            translateX = 0;
            translateY = 0;
            updateTransform();
        }

        // Dragging functionality
        mapContainer.addEventListener('mousedown', (e) => {
            if (e.target.closest('.point')) return;
            isDragging = true;
            startX = e.clientX - translateX;
            startY = e.clientY - translateY;
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            translateX = e.clientX - startX;
            translateY = e.clientY - startY;
            updateTransform();
        });

        document.addEventListener('mouseup', () => {
            isDragging = false;
        });

        // Zoom with mouse wheel (zoom to pointer position)
        mapContainer.addEventListener('wheel', (e) => {
            e.preventDefault();
            
            const rect = mapContainer.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;
            
            const oldScale = scale;
            const delta = e.deltaY > 0 ? 0.9 : 1.1;
            scale = Math.max(0.5, Math.min(5, scale * delta));
            
            // Adjust translation to zoom towards mouse pointer
            translateX = mouseX - (mouseX - translateX) * (scale / oldScale);
            translateY = mouseY - (mouseY - translateY) * (scale / oldScale);
            
            updateTransform();
        });

        // Initialize
        loadMap();
    </script>
</body>
</html>
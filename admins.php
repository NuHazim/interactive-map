<?php include 'database.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive Map - Admin Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
            background: #f093fb;
            color: white;
        }

        .btn-primary:hover {
            background: #e082ea;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(240, 147, 251, 0.4);
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
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

        .map-container.adding-point {
            cursor: crosshair !important;
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

        .point.dragging {
            cursor: move;
            opacity: 0.7;
            z-index: 30;
            transition: none;
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

        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            z-index: 1000;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal.active {
            display: block;
            animation: modalIn 0.3s ease-out;
        }

        @keyframes modalIn {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 999;
        }

        .modal-overlay.active {
            display: block;
        }

        .modal-header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 20px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 1.5em;
            margin: 0;
        }

        .close-modal {
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

        .close-modal:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #f093fb;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .color-picker {
            width: 60px;
            height: 40px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
        }

        .image-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 8px;
            display: none;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 25px;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 2000;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .notification.success {
            background: #28a745;
        }

        .notification.error {
            background: #dc3545;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
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
            <h1>⚙️ Admin Panel - Interactive Map</h1>
            <p>Upload map, add and manage points</p>
        </div>

        <div class="controls">
            <button class="btn btn-success" onclick="document.getElementById('mapUpload').click()">
                <span>📤</span> Upload New Map
            </button>
            <input type="file" id="mapUpload" accept="image/*" style="display: none;" onchange="uploadMap()">
            
            <button class="btn btn-success" onclick="enableAddPoint()">
                <span>📍</span> Add Point
            </button>
            
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
            <button class="fullscreen-btn" id="fullscreenBtn" onclick="toggleFullscreen()">⛶</button>
            <div class="map-wrapper" id="mapWrapper">
                <img src="" alt="Map" class="map-image" id="mapImage">
            </div>
        </div>
    </div>

    <!-- Point Info Popup -->
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
            <button class="popup-edit-btn" id="popupEditBtn" onclick="editFromPopup()">Edit Point</button>
        </div>
    </div>

    <!-- Point Form Modal -->
    <div class="modal-overlay" id="modalOverlay" onclick="closeModal()"></div>
    <div class="modal" id="pointModal">
        <div class="modal-header">
            <h3 id="modalTitle">Add Point</h3>
            <button class="close-modal" onclick="closeModal()">×</button>
        </div>
        <div class="modal-body">
            <form id="pointForm" onsubmit="savePoint(event)">
                <input type="hidden" id="pointId" value="">
                <input type="hidden" id="pointX" value="">
                <input type="hidden" id="pointY" value="">
                
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" id="pointTitle" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="pointDescription"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Image</label>
                    <input type="file" id="pointImage" accept="image/*" onchange="previewImage()">
                    <img id="imagePreview" class="image-preview">
                </div>
                
                <div class="form-group">
                    <label>Link (URL)</label>
                    <input type="url" id="pointLink" placeholder="https://example.com">
                </div>
                
                <div class="form-group">
                    <label>Icon Color</label>
                    <input type="color" id="pointColor" class="color-picker" value="#FF0000">
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-danger" id="deleteBtn" onclick="deletePoint()" style="display: none;">Delete</button>
                    <button type="button" class="btn btn-primary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Point</button>
                </div>
            </form>
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
        let isAddingPoint = false;
        let draggedPoint = null;
        let dragStartX, dragStartY;
        let dragMoved = false;
        let currentEditingPoint = null;

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
                        centerMap();
                        loadPoints();
                    };
                } else {
                    mapContainer.innerHTML = '<div class="loading">No map available. Please upload a map.</div>';
                }
            } catch (error) {
                console.error('Error loading map:', error);
                showNotification('Error loading map', 'error');
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
            document.querySelectorAll('.point').forEach(p => p.remove());
            
            points.forEach(point => {
                const pointEl = document.createElement('div');
                pointEl.className = 'point';
                pointEl.style.left = point.x_coordinate + '%';
                pointEl.style.top = point.y_coordinate + '%';
                pointEl.dataset.id = point.id;
                
                pointEl.innerHTML = `
                    <div class="point-pulse" style="background: ${point.icon_color};"></div>
                    <div class="point-icon" style="background: ${point.icon_color};"></div>
                `;
                
                // Mousedown to start drag
                pointEl.addEventListener('mousedown', (e) => {
                    if (!isAddingPoint) {
                        e.stopPropagation();
                        draggedPoint = pointEl;
                        dragStartX = e.clientX;
                        dragStartY = e.clientY;
                        dragMoved = false;
                    }
                });
                
                // Click to view (only if not dragged)
                pointEl.addEventListener('click', (e) => {
                    if (!isAddingPoint && !dragMoved) {
                        e.stopPropagation();
                        showPopup(point);
                    }
                });
                
                mapWrapper.appendChild(pointEl);
            });
        }

        async function uploadMap() {
            const file = document.getElementById('mapUpload').files[0];
            if (!file) return;
            
            const formData = new FormData();
            formData.append('action', 'upload_map');
            formData.append('map_image', file);
            
            try {
                const response = await fetch('database.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Map uploaded successfully!', 'success');
                    loadMap();
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                showNotification('Error uploading map', 'error');
            }
        }

        function enableAddPoint() {
            isAddingPoint = true;
            mapContainer.classList.add('adding-point');
            showNotification('Click on the map to add a point', 'success');
        }

        mapContainer.addEventListener('click', (e) => {
            if (isAddingPoint && e.target.closest('#mapImage')) {
                const rect = mapImage.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;
                
                openPointForm(x, y);
                isAddingPoint = false;
                mapContainer.classList.remove('adding-point');
            }
        });

        function openPointForm(x, y, point = null) {
            closePopup(); // Close info popup if open
            document.getElementById('pointId').value = point ? point.id : '';
            document.getElementById('pointX').value = x;
            document.getElementById('pointY').value = y;
            document.getElementById('pointTitle').value = point ? point.title : '';
            document.getElementById('pointDescription').value = point ? point.description : '';
            document.getElementById('pointLink').value = point ? point.link : '';
            document.getElementById('pointColor').value = point ? point.icon_color : '#FF0000';
            
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('imagePreview').src = '';
            
            if (point && point.image) {
                document.getElementById('imagePreview').src = point.image;
                document.getElementById('imagePreview').style.display = 'block';
            }
            
            document.getElementById('modalTitle').textContent = point ? 'Edit Point' : 'Add Point';
            document.getElementById('deleteBtn').style.display = point ? 'block' : 'none';
            
            document.getElementById('pointModal').classList.add('active');
            document.getElementById('modalOverlay').classList.add('active');
        }

        function showPopup(point) {
            currentEditingPoint = point;
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
            currentEditingPoint = null;
        }

        function editFromPopup() {
            if (currentEditingPoint) {
                closePopup();
                editPoint(currentEditingPoint);
            }
        }

        function editPoint(point) {
            openPointForm(point.x_coordinate, point.y_coordinate, point);
        }

        function closeModal() {
            document.getElementById('pointModal').classList.remove('active');
            document.getElementById('modalOverlay').classList.remove('active');
            document.getElementById('pointForm').reset();
        }

        async function savePoint(e) {
            e.preventDefault();
            
            const formData = new FormData();
            const pointId = document.getElementById('pointId').value;
            
            formData.append('action', pointId ? 'update_point' : 'add_point');
            if (pointId) formData.append('id', pointId);
            formData.append('title', document.getElementById('pointTitle').value);
            formData.append('description', document.getElementById('pointDescription').value);
            formData.append('link', document.getElementById('pointLink').value);
            formData.append('x_coordinate', document.getElementById('pointX').value);
            formData.append('y_coordinate', document.getElementById('pointY').value);
            formData.append('icon_color', document.getElementById('pointColor').value);
            
            const imageFile = document.getElementById('pointImage').files[0];
            if (imageFile) {
                formData.append('point_image', imageFile);
            }
            
            try {
                const response = await fetch('database.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    showNotification(result.message, 'success');
                    closeModal();
                    loadPoints();
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                showNotification('Error saving point', 'error');
            }
        }

        async function deletePoint() {
            if (!confirm('Are you sure you want to delete this point?')) return;
            
            const pointId = document.getElementById('pointId').value;
            const formData = new FormData();
            formData.append('action', 'delete_point');
            formData.append('id', pointId);
            
            try {
                const response = await fetch('database.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    showNotification(result.message, 'success');
                    closeModal();
                    loadPoints();
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                showNotification('Error deleting point', 'error');
            }
        }

        function previewImage() {
            const file = document.getElementById('pointImage').files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        // Point dragging
        document.addEventListener('mousemove', async (e) => {
            if (draggedPoint) {
                const moveThreshold = 5; // pixels
                const deltaX = Math.abs(e.clientX - dragStartX);
                const deltaY = Math.abs(e.clientY - dragStartY);
                
                if (deltaX > moveThreshold || deltaY > moveThreshold) {
                    dragMoved = true;
                    draggedPoint.classList.add('dragging');
                }
                
                if (dragMoved) {
                    const rect = mapImage.getBoundingClientRect();
                    const x = ((e.clientX - rect.left) / rect.width) * 100;
                    const y = ((e.clientY - rect.top) / rect.height) * 100;
                    
                    draggedPoint.style.left = x + '%';
                    draggedPoint.style.top = y + '%';
                }
                return;
            }
            
            if (isDragging && !isAddingPoint) {
                translateX = e.clientX - startX;
                translateY = e.clientY - startY;
                updateTransform();
            }
        });

        document.addEventListener('mouseup', async (e) => {
            if (draggedPoint) {
                if (dragMoved) {
                    const pointId = draggedPoint.dataset.id;
                    const point = points.find(p => p.id == pointId);
                    
                    const rect = mapImage.getBoundingClientRect();
                    const x = ((e.clientX - rect.left) / rect.width) * 100;
                    const y = ((e.clientY - rect.top) / rect.height) * 100;
                    
                    // Update point position
                    const formData = new FormData();
                    formData.append('action', 'update_point');
                    formData.append('id', pointId);
                    formData.append('title', point.title);
                    formData.append('description', point.description);
                    formData.append('link', point.link);
                    formData.append('x_coordinate', x);
                    formData.append('y_coordinate', y);
                    formData.append('icon_color', point.icon_color);
                    
                    try {
                        await fetch('database.php', { method: 'POST', body: formData });
                        loadPoints();
                    } catch (error) {
                        console.error('Error updating point position:', error);
                    }
                }
                
                draggedPoint.classList.remove('dragging');
                draggedPoint = null;
            }
            isDragging = false;
        });

        // Map dragging
        mapContainer.addEventListener('mousedown', (e) => {
            if (!isAddingPoint && !e.target.closest('.point')) {
                isDragging = true;
                startX = e.clientX - translateX;
                startY = e.clientY - translateY;
            }
        });

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
            centerMap();
            updateTransform();
        }

        function centerMap() {
            const containerRect = mapContainer.getBoundingClientRect();
            const imageRect = mapImage.getBoundingClientRect();
            
            translateX = (containerRect.width - imageRect.width) / 2;
            translateY = (containerRect.height - imageRect.height) / 2;
        }

        function toggleFullscreen() {
            const fullscreenBtn = document.getElementById('fullscreenBtn');
            
            if (!mapContainer.classList.contains('fullscreen')) {
                mapContainer.classList.add('fullscreen');
                fullscreenBtn.textContent = '✕';
            } else {
                mapContainer.classList.remove('fullscreen');
                fullscreenBtn.textContent = '⛶';
            }
        }

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

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Initialize
        loadMap();
    </script>
</body>
</html>
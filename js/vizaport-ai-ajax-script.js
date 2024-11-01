
// Merge default values with query parameters from the script tag
var vz_baseColor = ajax_object.baseColor || "#333";
var vz_launcherType = ajax_object.launcherType || "icon";
const vz_tabText = ajax_object.tabText || "AI Chat";
const vz_initialPlaceholder = ajax_object.initialPlaceholder || "Enter your question";
const vz_loadingMessage = ajax_object.loadingMessage || "Processing...";
const vz_defaultPosition = ajax_object.defaultPosition || "right";
const vz_titleColor = ajax_object.titleColor || "#fff";
const vz_userTextColor = ajax_object.userTextColor || "#888";
const vz_chatTextColor = ajax_object.chatTextColor || "#333";
const vz_defaultWidth = ajax_object.defaultWidth || "400px";
const vz_defaultHeight = ajax_object.defaultHeight || "450px";
const vz_fontFamily = ajax_object.fontFamily || "Arial";
const vz_fontSize = ajax_object.fontSize || "16px";

const vz_baseUrl = ajax_object.base_url;
const vz_chatCircleDark = vz_baseUrl + 'assets/img/vz-ai-chat-dark.svg';
const vz_chatCircleLight = vz_baseUrl + 'assets/img/vz-ai-chat-light.svg';
var vz_imageSrc = generateAiIcon(vz_baseColor, 45, 45);;

// Check if base color hex code is three digits
if (/^#[A-Fa-f0-9]{3}$/.test(vz_baseColor)) {
  vz_baseColor = `#${vz_baseColor[1]}${vz_baseColor[1]}${vz_baseColor[2]}${vz_baseColor[2]}${vz_baseColor[3]}${vz_baseColor[3]}`;
}

// Check if the hex code is valid (3 or 6 digits after #) and change icon if light color to dark
if (/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/.test(vz_baseColor)) {
  const vz_rgb = vz_hexTorgb(vz_baseColor);
  const vz_lightness = (vz_rgb.r * 0.299 + vz_rgb.g * 0.587 + vz_rgb.b * 0.114) / 255;
  vz_imageSrc = vz_lightness > 0.8 ? generateAiIcon('black', 45, 45) : generateAiIcon('white', 45, 45);
}

function vz_getScriptParameters(script) {
  const scriptSrc = script.src;
  const scriptParamsIndex = scriptSrc.indexOf('?');
  if (scriptParamsIndex !== -1) {
    const queryString = scriptSrc.substring(scriptParamsIndex + 1);
    const urlSearchParams = new URLSearchParams(queryString);
    return Object.fromEntries(urlSearchParams.entries());
  }
  return {};
}

// Create chat container
const vz_chatContainer = document.createElement("div");
vz_chatContainer.id = "help-chat-container";
document.body.appendChild(vz_chatContainer);

// Create chat tab
const vz_chatTab = document.createElement("div");
const vz_chatTabImageDiv = document.createElement("div");
vz_chatTabImageDiv.id = "help-chat-image-div";
const vz_chatImage = document.createElement("img");
const vz_textSpan = document.createElement("div");
vz_chatImage.src = generateAiIcon(vz_baseColor, 45, 45);
vz_textSpan.innerText = vz_tabText;
vz_chatTab.id = "help-chat-tab";
vz_chatTabImageDiv.appendChild(vz_chatImage);
vz_chatTab.appendChild(vz_chatTabImageDiv);
vz_chatTab.appendChild(vz_textSpan);
vz_chatContainer.appendChild(vz_chatTab);

// Create chat icon
const vz_chatIcon = document.createElement("div");
vz_chatIcon.style.display = "block";
if (vz_launcherType == "icon") {
  vz_chatIcon.id = "help-chat-icon";
  vz_chatIcon.style.backgroundImage = `url(${vz_imageSrc})`;
  vz_chatIcon.style.backgroundColor = vz_baseColor;
} else {
  vz_chatIcon.id = "help-chat-icon-tab";
  vz_chatIcon.innerText = vz_tabText;
}
vz_chatIcon.addEventListener("click", vz_toggleChat);
document.body.appendChild(vz_chatIcon);

// Create chat box
const vz_chatBox = document.createElement("div");
vz_chatBox.id = "help-chat-box";
if (window.innerWidth < 600) {
  vz_chatBox.style.width = "100%";
  vz_chatBox.style.height = "60vh";
} else {
	vz_chatBox.style.width = vz_defaultWidth;
	vz_chatBox.style.height = vz_defaultHeight;
}
vz_chatContainer.appendChild(vz_chatBox);

// Create close button
const vz_closeButton = document.createElement("div");
vz_closeButton.id = "help-chat-close";
vz_closeButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="16" height="16"> <path fill="currentColor" d="M210.8 256l153.6-153.6c9.4-9.4 9.4-24.6 0-33.9l-22.6-22.6c-9.4-9.4-24.6-9.4-33.9 0L192 199.2 38.4 45.6c-9.4-9.4-24.6-9.4-33.9 0L4.5 68.2c-9.4 9.4-9.4 24.6 0 33.9L158.2 256 4.5 409.6c-9.4 9.4-9.4 24.6 0 33.9l22.6 22.6c9.4 9.4 24.6 9.4 33.9 0L192 312.8l153.6 153.6c9.4 9.4 24.6 9.4 33.9 0l22.6-22.6c9.4-9.4 9.4-24.6 0-33.9L210.8 256z"/></svg>';
vz_closeButton.addEventListener("click", function() {
  vz_closeChat();
});
vz_chatTab.appendChild(vz_closeButton);

// Create footer section...
const vz_chatFooterSection = document.createElement("div");
vz_chatFooterSection.id = "help-chat-footer-section";

const vz_chatInputSection = document.createElement("div");
vz_chatInputSection.id = "help-chat-input-section";

// Create a chat input container
const vz_inputContainer = document.createElement("div");
vz_inputContainer.id = "help-chat-input-container";

// Create chat input
const vz_chatInput = document.createElement("textarea");
vz_chatInput.placeholder = vz_initialPlaceholder;
vz_chatInput.addEventListener('input', function() {
  this.style.height = '0';
  if(this.scrollHeight < 150){
    this.style.height = (this.scrollHeight) + 'px';
  }else{
    this.style.height = '150px';
  }
});
vz_chatInput.addEventListener("keydown", vz_handleChatInput);
vz_inputContainer.appendChild(vz_chatInput);

const inputShadow = vz_inputContainer.attachShadow({ mode: "open" });

const textarea_styles = `
textarea {
  flex: 1;
  font-family: "${vz_fontFamily}", serif;
  font-size: ${vz_fontSize};
  background-color: #f5f5f5;
  border:none;
  overflow-wrap: break-word;
  outline: none;
  border-radius: 18px;
  padding: 15px 10px 0 10px;
  resize: none;
}

textarea::-webkit-scrollbar {
  width: 5px;
  margin-left: -20px;
  padding-top: 5px;
  padding-bottom: 5px;
}

textarea::-webkit-scrollbar-track {
  background: transparent;
}

textarea::-webkit-scrollbar-thumb {
  background-color: #ccc;
  border-radius: 4px;
}

textarea::-webkit-scrollbar-button {
  display: none;
}
`;
const inputShadowStyle = document.createElement("style");
inputShadow.appendChild(inputShadowStyle);
inputShadowStyle.textContent = textarea_styles;
inputShadow.appendChild(vz_chatInput);

// Append the container to the chat box
vz_chatInputSection.appendChild(vz_inputContainer);

// Create send button
const vz_sendBtn = document.createElement("div");
vz_sendBtn.id = "help-chat-send-btn";
vz_chatInputSection.appendChild(vz_sendBtn);

const vz_sendIcon = document.createElement("span");
vz_sendIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="16" height="16" transform="rotate(135)"><path fill="currentColor" d="M499.8 22.7c-7.7-6.9-19.8-.6-19.8 8.6v128.5l-160.7 29.6 160.7 107.1V480c0 9.1 12.1 15.5 19.8 8.6l-437.5-231c-5.4-2.9-5.4-10 0-12.9l437.5-231c7.8-4.2 7.8-15.1 0-19.2z"/></svg>';
vz_sendIcon.classList.add("send-icon");
vz_sendIcon.addEventListener("click", function () {
  vz_processChat();
});

vz_sendBtn.appendChild(vz_sendIcon);

vz_chatFooterSection.appendChild(vz_chatInputSection);
vz_chatContainer.appendChild(vz_chatFooterSection);


// Create chat response window
const vz_chatResponseWindow = document.createElement("div");
vz_chatResponseWindow.id = "help-chat-response-window";
vz_chatBox.appendChild(vz_chatResponseWindow);

// Function to close the chat box
function vz_closeChat() {
  vz_chatContainer.style.display = 'none';
  vz_chatIcon.style.display = "block";
}

// Function to toggle the chat box visibility
function vz_toggleChat() {
  vz_chatContainer.style.display = 'block';
  vz_chatIcon.style.display = "none";
}

// Function to process user chat
function vz_processChat() {
  const vz_userInput = vz_chatInput.value.trim();
  if (vz_userInput !== "") {
    vz_addUserInputToHistory(vz_userInput);
    vz_queryBackend(vz_userInput);
    vz_chatInput.value = "";
  }
}

// Function to handle user input
function vz_handleChatInput(event) {
  event.stopPropagation();
  if (event.key === "Enter" || event.keyCode === 13) {
    event.preventDefault();
    vz_processChat();
  }
}

// Function to add user input to the chat history
function vz_addUserInputToHistory(vz_userInput) {
  const vz_chatResponseWindow = document.getElementById("help-chat-response-window");
  const vz_userInputElement = document.createElement("p");
  vz_userInputElement.classList.add("user-message"); // Add a class for styling
  vz_userInputElement.textContent = vz_userInput;
  vz_chatResponseWindow.appendChild(vz_userInputElement);
  vz_chatResponseWindow.scrollTop = vz_chatResponseWindow.scrollHeight;
}

// Query the Chat Response
function vz_queryBackend(vz_userInput) {

  vz_updateChatResponse(vz_loadingMessage, true);

  // Make an asynchronous request to the backend with nonce
  jQuery.ajax({
      type: 'POST',
      url: ajax_object.ajax_url,
      data: {
          action: 'vizaport_ai_ajax_action',
          _ajax_nonce: ajax_object._ajax_nonce,
          question: vz_userInput,
      },
      success: function(response) {
          // Log the entire response to the console
          console.log(response);

          // Check if the 'data' property is present
          if (response.hasOwnProperty('data') && response.data.hasOwnProperty('ai_response')) {
              const vz_aiResponse = response.data.ai_response;
              vz_updateChatResponse(vz_aiResponse, false, true);
          } else {
              vz_updateChatResponse("Sorry, there was an unknown error.", false, true);
          }
      },
      error: function(error) {
          console.log('AJAX error:', error);

          // Handle error by updating the chat response
          vz_updateChatResponse("An error occurred while processing your request.");
      },
      complete: function() {
          // Remove the loading message once the response is received or an error occurs
          const vz_chatResponseWindow = document.getElementById("help-chat-response-window");
          const vz_loadingMessageElement = vz_chatResponseWindow.querySelector('.loading-message');
          if (vz_loadingMessageElement) {
            vz_chatResponseWindow.removeChild(vz_loadingMessageElement);
          }
      },
  });
}

function generateAiIcon(color, width, height){
  const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
  svg.setAttribute("width", width);
  svg.setAttribute("height", height);
  svg.setAttribute("viewBox", "0 0 44 44");
  svg.setAttribute("fill", "none");

  svg.innerHTML = `
    <path d="M9.77703 8.92871C8.73266 8.92871 7.86877 9.79217 7.86877 10.8366V21.6537C7.86877 22.698 8.73235 23.5617 9.77703 23.5617H11.6316V27.5322C11.6318 28.2214 12.4973 28.5287 12.9321 27.994L15.8651 24.3852V27.5322C15.8651 28.5769 16.7295 29.4402 17.7738 29.4402H26.5389L30.1412 33.8729C30.576 34.4077 31.4415 34.1003 31.4417 33.4112V29.4402H33.2967C34.3407 29.4402 35.2047 28.5769 35.2047 27.5321V16.7156C35.2047 15.6709 34.3407 14.8076 33.2967 14.8076H27.2083V10.8366C27.2083 9.79198 26.3442 8.92871 25.2997 8.92871H9.77703ZM9.77703 10.393H25.2998C25.5587 10.393 25.744 10.5784 25.744 10.8366V21.6537C25.744 21.9114 25.5584 22.0973 25.2998 22.0973H16.1863C16.0774 22.0973 15.9699 22.1216 15.8716 22.1684C15.7733 22.2151 15.6867 22.2832 15.618 22.3677L13.0962 25.4703V22.8295C13.0962 22.4251 12.7684 22.0973 12.364 22.0973H9.77721C9.51893 22.0973 9.33355 21.9115 9.33355 21.6537V10.8365C9.33355 10.5782 9.51868 10.3929 9.77721 10.3929L9.77703 10.393Z" fill="${color}"/>
    <path d="M14.0893 12.603C13.8951 12.603 13.7089 12.6802 13.5716 12.8175C13.4343 12.9548 13.3571 13.141 13.3571 13.3352V19.2272C13.3571 19.4214 13.4343 19.6076 13.5716 19.7449C13.7089 19.8821 13.8952 19.9593 14.0893 19.9593C14.2835 19.9593 14.4697 19.8821 14.607 19.7449C14.7443 19.6076 14.8215 19.4214 14.8215 19.2272V17.673H17.3105V19.2399C17.3106 19.4341 17.3877 19.6203 17.525 19.7576C17.6624 19.8949 17.8486 19.972 18.0428 19.972C18.2369 19.972 18.4231 19.8949 18.5605 19.7576C18.6978 19.6203 18.7749 19.4341 18.775 19.2399V13.3352C18.7749 13.141 18.6978 12.9548 18.5605 12.8175C18.4232 12.6802 18.2369 12.603 18.0428 12.603H14.0893ZM14.8215 14.0674H17.3105V16.2085H14.8215V14.0674ZM21.5243 12.603C21.3301 12.603 21.1438 12.6802 21.0065 12.8175C20.8692 12.9548 20.7921 13.141 20.7921 13.3352V19.2271C20.7921 19.4213 20.8692 19.6076 21.0065 19.7449C21.1438 19.8822 21.3301 19.9593 21.5243 19.9593C21.7185 19.9593 21.9047 19.8822 22.042 19.7449C22.1793 19.6076 22.2565 19.4213 22.2565 19.2271V13.3352C22.2565 13.141 22.1793 12.9548 22.042 12.8175C21.9047 12.6802 21.7185 12.603 21.5243 12.603Z" fill="${color}"/>
  `;

  const svgData = new XMLSerializer().serializeToString(svg);
  const dataURL = "data:image/svg+xml;base64," + btoa(svgData);

  return dataURL;
}

// Chat Response
function vz_updateChatResponse(vz_response, vz_isProcessing = false, vz_aiResponse = false) {
  const vz_chatResponseWindow = document.getElementById("help-chat-response-window");

  const vz_systemReponseDiv = document.createElement('div');
  vz_systemReponseDiv.id = "vz-system-response";

  const vz_systemReponseAvatar = document.createElement('div');
  vz_systemReponseAvatar.id = "vz-system-response-avatar";
  const vz_aiAvatarDiv = document.createElement('div');
  vz_aiAvatarDiv.id = "vz-ai-avatar-div";
  const vz_aiAvatarImg = document.createElement("img");
  vz_aiAvatarImg.src = generateAiIcon(vz_baseColor, 33, 33);

  vz_aiAvatarImg.classList.add('avatar-image');
  vz_aiAvatarDiv.appendChild(vz_aiAvatarImg);
  vz_systemReponseAvatar.appendChild(vz_aiAvatarDiv);
  vz_systemReponseDiv.appendChild(vz_systemReponseAvatar);

  const vz_responseParagraph = document.createElement("p");
  vz_responseParagraph.innerHTML = makeClickableLinks(vz_response);
  vz_responseParagraph.classList.add("vz-system-message");

  if (vz_isProcessing) {
    vz_systemReponseDiv.classList.add("loading-message");
  }
  vz_systemReponseDiv.appendChild(vz_responseParagraph);
  vz_chatResponseWindow.appendChild(vz_systemReponseDiv);
  vz_chatResponseWindow.scrollTop = vz_chatResponseWindow.scrollHeight;
}

// Function to make clickable link if URL in vz_response
function makeClickableLinks(vz_response) {
  const vz_urlRegex = /(?:https?:\/\/|www\.[^\s]+)/g;
  return vz_response.replace(vz_urlRegex, function (vz_url) {
    let vz_url_text = vz_url;
    if (!vz_url.match(/^https?:\/\//)) {
      vz_url = "http://" + vz_url;
    }
    return `<a href="${vz_url}" target="_blank" rel="noopener noreferrer">${vz_url_text}</a>`;
  });
}

// Function to get RGB value from hex
function vz_hexTorgb(hex) {
  const vz_result = /^#?([A-Fa-f0-9]{2})([A-Fa-f0-9]{2})([A-Fa-f0-9]{2})$/i.exec(hex);
  if (vz_result) {
    return {
      r: parseInt(vz_result[1], 16),
      g: parseInt(vz_result[2], 16),
      b: parseInt(vz_result[3], 16)
    };
  } else {
    console.error("Invalid hex code:", hex);
    return { r: 0, g: 0, b: 0 };
  }
}

// Apply styles programmatically
const vz_styles = `
#help-chat-container {
  font-family: "${vz_fontFamily}", serif;
  font-size: ${vz_fontSize};
  text-align: left;
  position: fixed;
  bottom: 10px;
  ${vz_defaultPosition}: 10px;
  z-index: 99999;
  border-radius: 10px;
  box-shadow: 0 0 20px 0 rgba(0, 0, 0, 0.5);
  background-color: ${vz_baseColor};
  line-height: 1.3em;
  display:none;
}

#help-chat-tab {
  display: flex;
  background-color: ${vz_baseColor};
  color: ${vz_titleColor};
  padding: 20px 10px;
  vertical-align: middle;
  align-items: center;
  text-align: center;
  line-height: 1;
  border-radius: 10px 10px 0 0;
  justify-content: space-between;
}

#help-chat-image-div{
  width: 45px;
  height: 45px;
  background-color: white;
  border-radius: 50%;
}

#help-chat-icon {
  background-color: ${vz_baseColor};
  background-size: cover;
  background-position: center;
  color: ${vz_titleColor};
  margin-${vz_defaultPosition}: 10px;
  position: fixed;
  bottom: 10px;
  ${vz_defaultPosition}: 10px;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  box-shadow: 0 0 8px grey;
  cursor: pointer;
  z-index: 99999;
  display: none;
}

#help-chat-box {
  display: flex;
  flex-direction: column;
  position: relative;
  background-color: #f7f9fc;
  border-radius: 18px 18px 0 0;
  overflow: hidden;
}

#help-chat-response-window {
  max-height: 100%;
  overflow-y: auto;
  padding: 10px;
  display: flex;
  flex-direction: column;
}

#vz-system-response{
  display:flex;
  justify-content: flex-start;
}

.vz-system-message {
  color: ${vz_chatTextColor};
  font-family: "${vz_fontFamily}", serif;
  font-size: ${vz_fontSize};
  padding: 10px 15px;
  margin: 8px 0;
  border-radius: 18px;
  background-color: #F1F5F8;
  display: inline-block;
  text-align: left;
}
#vz-system-response-avatar {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  padding-bottom:15px;
}

#vz-ai-avatar-div{
  width: 30px;
  height: 30px;
  background-color: #F1F5F8;
  border-radius: 50%;
  margin-top: auto;
}

.avatar-image{
  width: 30px;
  height: 30px;
}

#help-chat-close {
  width: 50px;
  cursor: pointer;
  color: ${vz_titleColor};
}

#help-chat-footer-section{
  display:flex;
  flex-direction:column;
  background-color: white;
  border-radius:0 0 10px 10px;
}

#help-chat-input-section {
  display:flex;
  justify-content: space-between;
  padding:10px;
  position: relative;
}

#help-chat-input-container {
  color: ${vz_userTextColor};
  text-align: center;
  flex: 1;
  position: relative;
  display: flex;
}

#help-chat-send-btn{
  height:50px;
  width:50px;
  border-radius:18px;
  margin-left:10px;
  background-color: ${vz_baseColor};
  display: flex;
  justify-content: center;
  align-items: center;
}

.send-icon {
  color: rgb(136, 136, 136);
  cursor: pointer;
  color: white
}

.user-message {
  align-self: flex-end;
  color: white;
  font-family: "${vz_fontFamily}", sans-serif;
  font-size: ${vz_fontSize};
  padding:10px 15px !important;
  margin: 8px 0;
  border-radius: 18px;
  background-color: ${vz_baseColor};
  display: inline-block;
  max-width: 80%;
}

@media (max-width: 600px) {
  /* Adjust styles for smaller screens */
  #help-chat-container {
    bottom: 0px;
    ${vz_defaultPosition}: 10px;
  }
  #help-chat-container.open {
    width: 80%;
  }
  #help-chat-box {
    height: auto;
    border: 1px solid ${vz_baseColor};
  }
}

`;

const vz_styleElement = document.createElement("style");
vz_styleElement.textContent = vz_styles;
document.head.appendChild(vz_styleElement);

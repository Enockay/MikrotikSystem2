const input1 = document.querySelector(".phone-input");
const prompt = document.querySelector(".prompt");
const formDiv = document.createElement("div");
const form = document.querySelector(".form");
const connectBack = document.createElement("button");
connectBack.className = "connectBack";
const confirmButton = document.createElement("button");
const alertInfo = document.createElement("h4");
alertInfo.style.color = 'red';
const phoneInput = document.querySelector(".phone");
connectBack.textContent = "connect Back";
const loading = document.querySelector(".loading");
loading.style.display = "none";

form.appendChild(connectBack);
form.appendChild(alertInfo);

document.addEventListener("DOMContentLoaded", function () {
    var button = document.querySelector('.view');
    var instructionDiv = document.querySelector('.instruction');

    button.addEventListener('click', function () {
        if (instructionDiv.style.display === 'none') {
            instructionDiv.style.display = 'block';
            button.textContent = 'Close Instructions';
        } else {
            instructionDiv.style.display = 'none';
            button.textContent = 'View Instructions';
        }
    });
});

const showPrompt = () => {
    checkPromptContent();
    prompt.style.display = "block";
};

const closePrompt = () => {
    prompt.textContent = "";
    prompt.style.display = "none";
    checkPromptContent();
};
//spinner activity;
const spinner = document.createElement("div");
spinner.className = 'loading-container';
const spinCircle = document.createElement("div");
spinCircle.className = 'loading-spinner';
spinner.textContent = "please wait..";
spinner.appendChild(spinCircle);

function formatPhoneNumber(phoneNumber) {
    // Remove non-numeric characters
    const numericOnly = phoneNumber.replace(/\D/g, '');

    // Check if the number starts with "0111"
    if (numericOnly.startsWith('0111') && numericOnly.length === 10) {
        // Format the number with the country code "254"
        return '254' + numericOnly.substring(1);
    } else {
        // Add the country code "254" to the entire number
        return '254' + numericOnly.substring(1)
    }
}
function submitConnectBack(time, phone) {
    const connectForm = document.createElement("form");
    connectForm.className = "connectForm";
    connectForm.method = "post";
    connectForm.action = "./authenticateApi.php";

    // Create an input field for the amount
    const amountInput = document.createElement("input");
    const phoneNumber = document.createElement("input");
    phoneNumber.type = "hidden";
    phoneNumber.name = "phoneNumber";
    phoneNumber.value = phone
    amountInput.type = "hidden";
    amountInput.name = "remainingTime";
    amountInput.value = time;

    // Append the input fields to the form
    connectForm.appendChild(amountInput);
    connectForm.appendChild(phoneNumber);

    // Append the form to the document
    document.body.appendChild(connectForm);

    // Submit the form
    connectForm.submit();
}
connectBack.addEventListener('click', async () => {
    const phone2 = phoneInput.value;
    if (phone2 == "") {
        alertInfo.textContent = "Input field is empty or incorrect input";
        alertInfo.style.color = "red"; // Set text color to red
    } else if (phone2.length !== 10) {
        alertInfo.textContent = "Your digits are less than required or more than";
        alertInfo.style.color = "red"; // Set text color to red
    } else if (!phone2.startsWith("0")) {
        alertInfo.textContent = "Phone number should start with 0";
        alertInfo.style.color = "red"; // Set text color to red
    } else {
        form.appendChild(spinner);
        loading.style.display = "block";
        const phoneNumber = phone2;
        const UserPhoneNumber = formatPhoneNumber(phone2);

        try {
            const response = await fetch('./connectBackUser.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ UserPhoneNumber }),
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            console.log(data);

            if (data.ResultCode === 0) {
                const remainingTime = data.RemainingTime;
                submitConnectBack(remainingTime, phoneNumber);
                alertInfo.textContent = `Welcome back user. Remaining time: ${remainingTime}`;
                alertInfo.style.color = "green"; // Set text color to green for success message
                form.appendChild(spinner);
            } else if (data.ResultCode === 1) {
                alertInfo.textContent = `Cannot share user details`;
                alertInfo.style.color = "red"; // Set text color to red
                form.removeChild(spinner);
            } else if (data.ResultCode === 2) {
                alertInfo.textContent = `Your package is expired or try connectBack if not`;
                alertInfo.style.color = "red"; // Set text color to red
                form.removeChild(spinner);
            } else {
                alertInfo.textContent = `Unexpected result from the server`;
                alertInfo.style.color = "red"; // Set text color to red
                form.removeChild(spinner);
            }
        } catch (error) {
            console.error('Error:', error);
            alertInfo.textContent = "Error occurred while verifying";
            alertInfo.style.color = "red"; // Set text color to red
            form.removeChild(spinner);
        }
    }
});

//form to connect user ;
function submitConnectForm(amount, phone) {
    const connectForm = document.createElement("form");
    connectForm.className = "connectForm";
    connectForm.method = "post";
    connectForm.action = "./public/connect.php";

    // Create input fields for the amount and phone number
    const amountInput = document.createElement("input");
    const phoneNumberInput = document.createElement("input");
    const timeInput = document.createElement("input");
    timeInput.type = "hidden";
    timeInput.name = 'remainingTime';
    timeInput.value = 'put_your_time_value_here'; // Set the value accordingly
    phoneNumberInput.type = "hidden";
    phoneNumberInput.name = "phoneNumber";
    phoneNumberInput.value = phone;
    amountInput.type = "hidden";
    amountInput.name = "amount";
    amountInput.value = amount;

    // Append the input fields to the form
    connectForm.appendChild(amountInput);
    connectForm.appendChild(phoneNumberInput);

    // Append the form to the document body
    document.body.appendChild(connectForm);

    // Submit the form
    connectForm.submit();
}

const purchaseItem = (value) => {
    const confirmButton = document.createElement("button");
    confirmButton.className = "connect";
    confirmButton.textContent = "connect";

    const checkInput = async (input) => {
        if (input.value == "") {
            alert("Input field is empty or incorrect input");
        } else if (input.value.length !== 10) {
            alert("Your digits are less than required or more than");
        } else {
            const phone2 = input.value;
            const phone = formatPhoneNumber(phone2);
            const Amount = extractAmount(value);
            const time1 = extractTime(value)
            console.log(`time is ${time1}`);
            console.log(phone);

            // Create a FormData object and append your form data
            const phoneNumber = phone;
            console.log(phoneNumber);
            // console.log(Amount);
            const timeUnit = time1;
            console.log(timeUnit);
            const formattedTimeUnit = `${timeUnit.value}-${timeUnit.unit}`;

            const formData = new FormData();
            formData.append('phoneNumber', phone);
            formData.append('Amount', Amount);
            formData.append('timeUnit', timeUnit);

            prompt.textContent = '';
            prompt.appendChild(spinner);

            try {
                const response = await fetch('https://blackie-networks-295df9ed8dbf.herokuapp.com/api/makePayment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ phoneNumber, Amount, timeUnit })
                });
                if (!response.ok) {
                    throw new Error("Network error");
                }

                const contentType = response.headers.get("content-type");
                const data = await response.json();
                //console.log(data);

                if (data && data.transactionId) {
                    setTimeout(() => {
                        prompt.textContent = "";
                        prompt.style.backgroundColor = "brown";
                        prompt.appendChild(confirmButton);
                    }, 10000);


                    const transactionId = data.transactionId;
                    console.log(transactionId);
                    // Set up event listener outside the timeout
                    // Update with your actual ID

                    confirmButton.addEventListener('click', async () => {
                        prompt.textContent = '';
                        prompt.appendChild(spinner);
                        try {
                            const callbackResponse = await fetch('./activeUser.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: phoneNumber
                            });

                            if (!callbackResponse.ok) {
                                throw new Error('Network response was not ok');
                            }

                            const callbackData = await callbackResponse.json();

                            console.log(callbackData);

                            if (callbackData && callbackData.ResultCode === 0) {
                                prompt.textContent = '';
                                prompt.appendChild(spinner);
                                submitConnectForm(Amount, phone2);
                            } else {
                                prompt.textContent = "transaction was unsuccessful";
                                prompt.style.color = "white";
                                // prompt.appendChild(confirmButton);
                                prompt.appendChild(close);
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            prompt.textContent = "error : in direct login try connecting with connectBack"
                            prompt.style.color = 'white';
                            prompt.appendChild(close);
                        }
                    });
                } else {
                    prompt.textContent = "transaction cancelled";
                    prompt.appendChild(close);
                    //  prompt.appendChild(confirmButton);
                }
            } catch (error) {
                // Handle the error response (if needed)
                prompt.textContent = "error in procesing transcation";
                prompt.appendChild(close);
                console.log("Error submitting form: " + error.message);
            }
        }
    };


    const form = document.createElement("form");
    form.className = "prompt1";
    form.method = "post";
    form.action = "mpesa2Proccess.php";

    const inputPhoneNumber = document.createElement("input");
    inputPhoneNumber.className = "phone-input";
    inputPhoneNumber.type = "number";
    inputPhoneNumber.name = "phoneNumber";
    inputPhoneNumber.placeholder = "Phone...";

    const inputAmount = document.createElement("input");
    inputAmount.type = "hidden";
    inputAmount.name = "amount";
    inputAmount.value = extractAmount(value);

    const inputTimeUnit = document.createElement("input");
    inputTimeUnit.type = "hidden";
    inputTimeUnit.name = "timeUnit";
    inputTimeUnit.value = extractTime(value)

    const para = document.createElement("p");
    para.className = "para";
    para.textContent = "Thanks for choosing us. Input your Mpesa Number below, then wait for the Mpesa prompt.";

    const para1 = document.createElement("p");
    para1.textContent = `Confirm purchase of ${value}`;

    const button = document.createElement("button");
    button.type = "submit";
    button.className = "pay";
    button.textContent = `pay ${inputAmount.value}`;

    // Attach the checkInput function to the form's submit event
    form.addEventListener('submit', async (event) => {
        event.preventDefault();  // Prevent the default form submission
        await checkInput(inputPhoneNumber);
    });

    const close = document.createElement("button");
    close.textContent = "Close";
    close.className = "close";
    close.addEventListener("click", closePrompt);

    form.appendChild(para);
    form.appendChild(para1);
    form.appendChild(inputPhoneNumber);
    form.appendChild(inputAmount);
    form.appendChild(inputTimeUnit);
    form.appendChild(button);
    form.appendChild(close);

    showPrompt();
    formDiv.appendChild(form);
    prompt.appendChild(formDiv);
};

const checkPromptContent = () => {
    formDiv.textContent = ""; // Clear the content of formDiv
};

const extractAmount = (value) => {
    const regex = /ksh=(\d+)/i;
    const match = value.match(regex);
    return match ? match[1] : "";
};

const extractTime = (value) => {
    const regex = /(\d+)-(hour|day|min)/i;
    const timeMatch = value.match(regex);

    // Declare allocatedTime using let
    let allocatedTime = null;

    if (timeMatch) {
        allocatedTime = {
            value: parseInt(timeMatch[1], 10),
            unit: timeMatch[2].toLowerCase(),
        };
    }

    // Return the extracted time information
    return allocatedTime;
};



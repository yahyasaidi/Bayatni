document.addEventListener('DOMContentLoaded', function () {
  const formSteps = document.querySelectorAll('.form');
  const signupForm = document.getElementById('signupForm');
  const nextButtons = document.querySelectorAll('.button-submit[type="button"]');

  let currentStep = 0;

  const nom = document.getElementById("nom");
  const prenom = document.getElementById("prenom");
  const bd = document.getElementById("birthday");

  const emailInput = document.getElementById("email");
  const passwordInput = document.getElementById("password");
  const confirmInput = document.getElementById("confirm_password");

  const cardName = document.getElementById("cardName");
  const cardNumber = document.getElementById("cardNumber");
  const expiryDate = document.getElementById("expiryDate");
  const cvv = document.getElementById("cvv");

  const nameError = document.getElementById("error-nom");
  const prenomError = document.getElementById("error-prenom");
  const birthdayError = document.getElementById("error-birthday");


  function showStep(stepIndex) {
    formSteps.forEach((form, index) => {
      form.style.display = index === stepIndex ? 'block' : 'none';
    });
  }

  function styleInput(input, isValid) {
    const el = input.parentElement || input;
    el.style.border = isValid ? "1px solid black" : "1px solid red";
    el.style.boxShadow = isValid ? "0 10px 30px black" : "0 5px 30px red";
  }

  function validateNameField(input, error) {
    const value = input.value.trim();
    const allowed = /^[A-Za-zÀ-ÖØ-öø-ÿ ]+$/;
    const isValid = allowed.test(value) && value.length > 0;
    styleInput(input, isValid);
    return isValid;
  }

  function validateBirthday() {
    const birthday = bd.value;
    if (!birthday) {
      birthdayError.textContent = "Please select a date";
      styleInput(bd, false);
      return false;
    }

    const birthDate = new Date(birthday);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    if (
      today.getMonth() < birthDate.getMonth() ||
      (today.getMonth() === birthDate.getMonth() && today.getDate() < birthDate.getDate())
    ) {
      age--;
    }

    const isValid = age >= 18;
    styleInput(bd, isValid);
    return isValid;
  }

  function validateEmail() {
    const email = emailInput.value.trim();
    const isValid = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email);
    styleInput(emailInput, isValid);
    return isValid;
  }

  function validatePassword() {
    const isValid = passwordInput.value.length >= 6;
    styleInput(passwordInput, isValid);
    return isValid;
  }

  function validateConfirmPassword() {
    const isValid = passwordInput.value === confirmInput.value;
    styleInput(confirmInput, isValid);
    return isValid;
  }

  function validateCardName() {
    const name = cardName.value.trim();
    const isValid = /^[A-Za-zÀ-ÿ' -]{2,}$/.test(name);
    styleInput(cardName, isValid);
    return isValid;
  }
  
  function validateCardNumber() {
    const isValid = /^\d{16}$/.test(cardNumber.value.replace(/\s+/g, ''));
    styleInput(cardNumber, isValid);
    return isValid;
  }

  function validateExpiryDate() {
    const isValid = /^(0[1-9]|1[0-2])\/\d{2}$/.test(expiryDate.value.trim());
    styleInput(expiryDate, isValid);
    return isValid;
  }

  function validateCVV() {
    const isValid = /^\d{3}$/.test(cvv.value.trim());
    styleInput(cvv, isValid);
    return isValid;
  }

  function validateStep(step) {
    switch (step) {
      case 0:
        return validateNameField(nom, nameError) &&
               validateNameField(prenom, prenomError) &&
               validateBirthday();
      case 1:
        return validateEmail() &&
               validatePassword() &&
               validateConfirmPassword();
      case 2:
        return validateCardName() &&
               validateCardNumber() &&
               validateExpiryDate() &&
               validateCVV();
      default:
        return false;
    }
  }

  nextButtons.forEach((button) => {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      if (validateStep(currentStep)) {
        currentStep++;
        showStep(currentStep);
      } else {
        alert("Please fix the errors before proceeding.");
      }
    });
  });

  nom.addEventListener("input", () => validateNameField(nom, nameError));
  prenom.addEventListener("input", () => validateNameField(prenom, prenomError));
  bd.addEventListener("input", validateBirthday);

  emailInput.addEventListener("input", validateEmail);
  passwordInput.addEventListener("input", () => {
    validatePassword();
    validateConfirmPassword();
  });
  confirmInput.addEventListener("input", validateConfirmPassword);

  cardName.addEventListener('input', validateCardName);
  cardNumber.addEventListener('input', validateCardNumber);
  expiryDate.addEventListener('input', validateExpiryDate);
  cvv.addEventListener('input', validateCVV);

  showStep(currentStep);
});

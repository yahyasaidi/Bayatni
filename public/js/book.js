document.getElementById('price').addEventListener('input', function() {
    document.getElementById('price-value').textContent = this.value + ' DT';
});

function showBookingForm(hotelId, hotelTitle, hotelPrice) {
    document.getElementById('modal-hotel-id').value = hotelId;
    document.getElementById('modal-hotel-title').textContent = hotelTitle;
    document.getElementById('modal-hotel-price').textContent = hotelPrice;
    
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    const checkoutDate = new Date(today);
    checkoutDate.setDate(checkoutDate.getDate() + 3);
    
    document.getElementById('modal-check-in').value = formatDate(tomorrow);
    document.getElementById('modal-check-out').value = formatDate(checkoutDate);

    document.getElementById('booking-details-form').classList.remove('hidden');
    document.getElementById('payment-form').classList.add('hidden');
    document.getElementById('confirm-btn').classList.remove('hidden');
    document.getElementById('pay-now-btn').classList.add('hidden');
    document.getElementById('pay-later-btn').classList.add('hidden');
    

    updateBookingSummary();
    document.getElementById('bookingModal').classList.remove('hidden');
}

function closeBookingModal() {
    document.getElementById('bookingModal').classList.add('hidden');
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function updateBookingSummary() {
    const checkIn = new Date(document.getElementById('modal-check-in').value);
    const checkOut = new Date(document.getElementById('modal-check-out').value);
    const pricePerNight = parseInt(document.getElementById('modal-hotel-price').textContent);
    const guests = document.getElementById('modal-guests').value;
    const roomType = document.getElementById('modal-room-type').value;

    let roomTypeMultiplier = 1;
    let roomTypeName = 'Standard';
    
    switch (roomType) {
        case 'deluxe':
            roomTypeMultiplier = 1.5;
            roomTypeName = 'Deluxe';
            break;
        case 'suite':
            roomTypeMultiplier = 2;
            roomTypeName = 'Suite';
            break;
        case 'family':
            roomTypeMultiplier = 1.8;
            roomTypeName = 'Familiale';
            break;
    }
    
    const timeDiff = checkOut.getTime() - checkIn.getTime();
    const nights = Math.ceil(timeDiff / (1000 * 3600 * 24));
    const subtotal = pricePerNight * roomTypeMultiplier * nights;
    const taxRate = 0.10;
    const tax = subtotal * taxRate;
    const total = subtotal + tax;

    document.getElementById('total-price').value = total;

    const options = { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' };
    const checkInFormatted = checkIn.toLocaleDateString('fr-FR', options);
    const checkOutFormatted = checkOut.toLocaleDateString('fr-FR', options);
    
    document.getElementById('booking-dates').textContent = `${checkInFormatted} → ${checkOutFormatted}`;
    document.getElementById('booking-nights').textContent = `${nights} nuit${nights > 1 ? 's' : ''}, ${guests} personne${guests > 1 ? 's' : ''}`;
    document.getElementById('booking-room-type').textContent = `Type de chambre: ${roomTypeName}`;
    document.getElementById('booking-price-per-night').textContent = `Prix par nuit: ${Math.round(pricePerNight * roomTypeMultiplier)} DT`;
    document.getElementById('booking-subtotal').textContent = `Sous-total: ${Math.round(subtotal)} DT`;
    document.getElementById('booking-tax').textContent = `Taxes (10%): ${Math.round(tax)} DT`;
    document.getElementById('booking-total').textContent = `Total: ${Math.round(total)} DT`;
}

function proceedToPayment() {
    document.getElementById('booking-details-form').classList.add('hidden');
    document.getElementById('payment-form').classList.remove('hidden');
    document.getElementById('confirm-btn').classList.add('hidden');
    document.getElementById('pay-now-btn').classList.remove('hidden');
    document.getElementById('pay-later-btn').classList.remove('hidden');
}

function processPayment() {

    document.getElementById('payment-status').value = 'confirmed';
    document.getElementById('booking-form').submit();

}

function payLater() {

    document.getElementById('payment-status').value = 'pending';
    document.getElementById('booking-form').submit();

}


document.addEventListener('DOMContentLoaded', function() {
    const checkInInput = document.getElementById('modal-check-in');
    const checkOutInput = document.getElementById('modal-check-out');
    const roomTypeSelect = document.getElementById('modal-room-type');
    const guestsSelect = document.getElementById('modal-guests');
    const cardSelectionSelect = document.getElementById('card-selection');

    if (checkInInput && checkOutInput) {
        checkInInput.addEventListener('change', updateBookingSummary);
        checkOutInput.addEventListener('change', updateBookingSummary);
    }

    if (roomTypeSelect) {
        roomTypeSelect.addEventListener('change', updateBookingSummary);
    }

    if (guestsSelect) {
        guestsSelect.addEventListener('change', updateBookingSummary);
    }
});
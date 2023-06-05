// add 3 months to lend date and set it as return date
document.getElementById('lendDate').addEventListener('change', function() {
    addMonths(3);
});

function addMonths(months) {
    const lendDate = document.getElementById('lendDate');
    const reuturnDate = document.getElementById('returnDate');

    console.log(lendDate.value);
    let date = new Date(lendDate.value);

    if (date != 'Invalid Date') {
        date.setMonth(date.getMonth() + months);
        date = date.toISOString().slice(0, 10);
        reuturnDate.value = date;
    }

    else {
        console.warn('lendDate is empty')
    }
}
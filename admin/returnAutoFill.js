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



// return book
let inputElement = document.getElementById('lentTo');
let classEl = document.getElementById('class');
let lendDateEl = document.getElementById('lendDate');
let returnDateEl = document.getElementById('returnDate');

let originalValue = inputElement.value;


inputElement.addEventListener('input', function(event) {
  // Získání aktuální hodnoty z inputu
  let value = event.target.value;

  // Podmínka: Zkontroluje, zda byl input vymazán a zda byl původně alespoň 5 znaků dlouhý
  if (value.length === 0 && originalValue.length >= 5) {
    // vymazat ostatni inputy
    classEl.value = '';
    lendDateEl.value = '';
    returnDateEl.value = '';
  }
});
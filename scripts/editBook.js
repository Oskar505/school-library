console.log('returnAutoFill.js');

function isEmpty(value) {
  return value == null || value === "" || (Array.isArray(value) && value.length === 0);
}



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



// cancel reservation
let reservationElement = document.getElementById('reservation');
let reservationExpEl = document.getElementById('reservationExpiration');

let reservationOriginalValue = reservationElement.value;

console.log(reservationElement.value);


reservationElement.addEventListener('input', function(event) {
  console.log('click');
  // Získání aktuální hodnoty z inputu
  let inputValue = event.target.value;

  // Podmínka: Zkontroluje, zda byl input vymazán a zda byl původně alespoň 5 znaků dlouhý
  if (inputValue.length === 0 && reservationOriginalValue.length >= 5) {
    // vymazat ostatni inputy
    reservationExpEl.value = '';
  }
});




// Username autofill

// Funkce pro aktualizaci našeptávacího seznamu
function updateSuggestions(inputId, suggestionsListId, warningIconId) {
  console.log(inputId);

  var input = document.getElementById(inputId).value;
  var datalist = document.getElementById(suggestionsListId);

  // Vymazání stávajících hodnot v datalistu
  while (datalist.firstChild) {
    datalist.removeChild(datalist.firstChild);
  }

  // AJAX požadavek na získání nových hodnot z databáze
  $.ajax({
    url: '/getDataForAjax/getUsernameSuggestions.php', // PHP soubor, který získává hodnoty z databáze
    type: 'GET',
    data: { query: input },
    success: function (response) {
      var suggestions = JSON.parse(response);
      console.log(suggestions);

      // Přidání nových hodnot do datalistu
      if (suggestions[0] != 'Tento uživatel neexistuje') {
        console.log('hide');
        let usernameWarning = document.getElementById(warningIconId);
        usernameWarning.style.display = 'none';
        
        // logins
        suggestions.forEach(function (value) {
          var option = document.createElement('option');
          option.value = value['login'];
          console.log(value['login']);
          datalist.append(option);
        });

        
        //class
        if (inputId == 'lentTo') {
          let lentToEl = document.getElementById('lentTo');
          let classEl = document.getElementById('class');

          if (lentToEl.value != '') {
            classEl.value = suggestions[0]['class'];
          }

          else {
            classEl.value = '';
          }
        }
        
      }
      
      else {
        console.log('show');
        let usernameWarning = document.getElementById(warningIconId);
        usernameWarning.style.display = 'inline-block';

        let classEl = document.getElementById('class');
        classEl.value = '';
      }
    },
    error: function () {
      console.error('Chyba při načítání dat.');
    }
  });
}

// Nastavení události input pro aktualizaci našeptávání
document.getElementById('lentTo').addEventListener('input', function() {
  updateSuggestions('lentTo', 'suggestionsList', 'lentToUsernameWarning');
})

document.getElementById('reservation').addEventListener('input', function() {
  updateSuggestions('reservation', 'reservationSuggestionsList', 'reservationUsernameWarning');
})




//infinite date

let infiniteBtn = document.getElementById('infiniteDateBtn');

infiniteBtn.addEventListener('click', function() {
  setInfiniteDate();
});


function setInfiniteDate() {
  console.log('infinite');
  returnDateEl.value = '2969-06-20';

  let today = new Date();
  let year = today.getFullYear();
  let month = (today.getMonth() + 1).toString().padStart(2, '0'); // Přidáváme 1, protože měsíce jsou číslovány od 0.
  let day = today.getDate().toString().padStart(2, '0');

  lendDateEl.value = year + '-' + month + '-' + day;
}




// SELECT INPUTS

let tableRows = document.querySelectorAll('.label');
let activatedInputs = [];
let selectedInputsEl = document.getElementById('selectedInputs');


if (selectedInputsEl.value != 'editOne') {
  tableRows.forEach(function(row) {
    row.classList.add('deactivateLine'); // deactivate all
  
    row.addEventListener('click', function(event) {
      let disabled = event.currentTarget.classList.contains('deactivateLine');
      let id = event.currentTarget.id;
  
      if (disabled && id != 15 && id != 17) { // activate
        row.classList.remove('deactivateLine');
  
  
        // add to list
        let index = activatedInputs.indexOf(id);
        if (index === -1) { // list doesnt contains id
          activatedInputs.push(id);
        }
  
        console.log(JSON.stringify(activatedInputs));
  
        selectedInputsEl.value = JSON.stringify(activatedInputs);
      }
  
  
      else {
        row.classList.add('deactivateLine');
  
  
        // delete from list
        let index = activatedInputs.indexOf(id);
        if (index !== -1) { // list contains id
          activatedInputs.splice(index, 1);
        }
  
        console.log(JSON.stringify(activatedInputs));
        
  
        selectedInputsEl.value = JSON.stringify(activatedInputs);
  
      }
  
      //console.log(selectedInputsEl.value);
    })
  })
}
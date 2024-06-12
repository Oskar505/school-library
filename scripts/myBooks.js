let reservedSection = document.getElementById('myBooksReserved');
let reservedTable = document.getElementById('reservedTable');
let borrowedSection = document.getElementById('myBooksBorrowed');
let borrowedTable = document.getElementById('borrowedTable');



$.ajax({
    url: '/getDataForAjax/myBooksData.php',
    type: 'GET',
    dataType: 'json',
    success: function(response) {

        if (response[0] != 'not logged in') {
            let reserved = response[0];
            let borrowed = response[1];
            let newLine;

            console.log(response);


            if (reserved[0]['id'] == '' && borrowed[0]['id'] == '') { // hide both tables
                let myBooksMain = document.getElementById('myBooksMain');
                let noBooksH2 = document.createElement('h2');

                noBooksH2.textContent = 'Zatím nemáte žádné knihy.';
                noBooksH2.className = 'noBooksH2';

                myBooksMain.innerHTML = '';
                myBooksMain.appendChild(noBooksH2);
            }


            else if (reserved[0]['id'] == '') { // hide reserved table
                let myBooksMain = document.getElementById('myBooksMain');
                myBooksMain.style.maxWidth = '500px';

                reservedSection.style.display = 'none';

            }


            else if (borrowed[0]['id'] == '') { // hide borrowed table
                let myBooksMain = document.getElementById('myBooksMain');
                myBooksMain.style.maxWidth = '500px';

                borrowedSection.style.display = 'none';
            }



            // reserved books table
            reserved.forEach(book => {
                endDate = new Date(book['endDate']);
                // endDate = `${endDate.getDate()}. ${endDate.getMonth() + 1}. ${endDate.getYear()}`;
                endDate = endDate.toLocaleDateString('cs-CZ')

                newLine = reservedTable.insertRow(); // add row
                newLine.className = 'myBooksTr'; // add class
                newLine.setAttribute('data-id', book['id']); // for redirecting to more info
                newLine.setAttribute('title', 'Více')
                newLine.insertCell(0).innerHTML = book['name']; // name columun
                newLine.insertCell(1).innerHTML = endDate; // end date column

                // split
                newLine = reservedTable.insertRow();
                newLine.className = 'tableSplit';
            });


            // borrowed books table
            borrowed.forEach(book => {
                endDate = new Date(book['endDate']);
                // endDate = `${endDate.getDate()}. ${endDate.getMonth() + 1}.`;
                endDate = endDate.toLocaleDateString('cs-CZ')

                newLine = borrowedTable.insertRow(); // add row
                newLine.className = 'myBooksTr'; // add class
                newLine.setAttribute('data-id', book['id']); // for redirecting to more info
                newLine.setAttribute('title', 'Více')
                newLine.insertCell(0).innerHTML = book['name']; // name columun
                newLine.insertCell(1).innerHTML = endDate; // end date column

                // split
                newLine = borrowedTable.insertRow();
                newLine.className = 'tableSplit';

                // add link

            });
        }
        


        else {
            let myBooksMain = document.getElementById('myBooksMain');
            let loginLink = document.createElement('a');

            loginLink.textContent = 'Pro zobrazení knih se přihlašte.';
            loginLink.href = '/login.php?redirect=myBooks.php';
            loginLink.className = 'notLoggedIn';

            myBooksMain.innerHTML = '';
            myBooksMain.appendChild(loginLink);
        }
    }
});



// REDIRECT TO moreInfo.php

reservedTable.addEventListener('click', function(event) {
    let target = event.target;

    console.log(target.tagName);
    
    if (target.tagName === 'TD') {
        let tr = target.closest('tr');

        if (tr) {
            let id = tr.getAttribute('data-id');

            console.log(id);

            if (id != null) {
                window.location.href = '/moreInfo.php?id=' + id;
            }
        }
    }
})


borrowedTable.addEventListener('click', function(event) {
    let target = event.target;

    console.log(target.tagName);
    
    if (target.tagName === 'TD') {
        let tr = target.closest('tr');

        if (tr) {
            let id = tr.getAttribute('data-id');

            console.log(id);

            if (id != null) {
                window.location.href = '/moreInfo.php?id=' + id;
            }
        }
    }
})




// show scroll up button
const elementToShow = document.getElementById('scrollUpBtn');
elementToShow.style.display = 'none';


function isOnTop(el) {
    const rect = el.getBoundingClientRect();
    const viewHeight = Math.max(document.documentElement.clientHeight, window.innerHeight);
    return !(rect.bottom < 0 || rect.top - viewHeight >= 0);
}

document.addEventListener("DOMContentLoaded", function() {
    const elementToCheck = document.getElementById('top');
    const elementToShow = document.getElementById('scrollUpBtn');

    if (!isOnTop(elementToCheck)) {
        elementToShow.style.display = 'block';
    }
});

window.addEventListener('scroll', function() {
    const elementToCheck = document.getElementById('top');
    const elementToShow = document.getElementById('scrollUpBtn');

    if (!isOnTop(elementToCheck)) {
        elementToShow.style.display = 'block';
        console.log('show')
    }

    else {
        elementToShow.style.display = 'none';
        console.log('hide')
    }
});
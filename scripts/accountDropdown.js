// account dropdown

// load dropdown
document.addEventListener('DOMContentLoaded', function(event) {
    
    toggleDropdown(event, true);
    
    console.log(event);
    
});

document.getElementById('accountCircle').addEventListener('click', function(event) {
    toggleDropdown(event)
})

function toggleDropdown(event, loadOnly=false) {

    if (!loadOnly && event != null) {
        let dropdown = document.getElementById("accountDropdown");

        if (dropdown.style.display == "block") {
            console.log('hide');
            dropdown.style.display = "none";
            document.removeEventListener('click', function(e) {
                closeDropdownOnClickOutside(e);
            });

            event.stopPropagation();
        }
        
        else {
            console.log('show');
            dropdown.style.display = "block";
            document.addEventListener('click', function(e) {
                closeDropdownOnClickOutside(e);
            });

            event.stopPropagation();
        }
    }

    else if (event == null) {
        console.error('event is null');
    }

    else {
        event.stopPropagation();
    }
}


function closeDropdownOnClickOutside(event) {
    var dropdown = document.getElementById("accountDropdown");
    var targetElement = event.target;

    if (!dropdown.contains(targetElement)) {
        dropdown.style.display = "none";
        document.removeEventListener('click', closeDropdownOnClickOutside);
    }
}


function getDataFromSession() {
    fetch('/getLogInData.php')
        .then(response => response.text())
        .then(data => {
            if (data != '') {
                let myBooksIcon = document.getElementById('myBooksIcon');
                let settingsIcon = document.getElementById('settingsIcon');

                myBooksIcon.style.setProperty('display', 'flex', 'important');
                settingsIcon.style.setProperty('display', 'flex', 'important');
            }

            else {
                let myBooksIcon = document.getElementById('myBooksIcon');
                let settingsIcon = document.getElementById('settingsIcon');

                myBooksIcon.style.setProperty('display', 'none', 'important');
                settingsIcon.style.setProperty('display', 'none', 'important');
            }
        })
        .catch(error => {
            console.error('Chyba při načítání dat z session:', error);
        });
}

getDataFromSession();
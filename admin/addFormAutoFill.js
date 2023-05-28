console.log('test')

const isbn = document.getElementById('isbn');
const fillBtn = document.getElementById('fillBtn');

fillBtn.addEventListener('click', function() {
    fill(isbn.value);
});


function fill(isbnVal) {
    console.log('fill');

    let isbn = isbnVal;

    let url = "https://www.googleapis.com/books/v1/volumes?q=isbn:" + isbn;

    console.log(url);
    
    fetch(url).then(response => {
        if (!response.ok) {
            throw new Error('Chyba při komunikaci s Google Books API: ' + response.status);
        }

        return response.json();
    })
    .then(data => {
        console.log(data)

        //data = JSON.parse(data);
        let name = data.items[0].volumeInfo.title;
        let author = data.items[0].volumeInfo.authors[0];
        let publisher = data.items[0].volumeInfo.publisher;

        if (publisher == 'CZECH') {
            publisher = '';
        }

        const nameInput = document.getElementById('name');
        const authorInput = document.getElementById('author');
        const publisherInput = document.getElementById('publisher');

        nameInput.value = name;
        authorInput.value = author;
        publisherInput.value = publisher;

        console.log(name);
        console.log(author);
        console.log(publisher);
    })
    .catch(error => {
        console.error('Chyba: ', error)
    })
        
    //AIzaSyBd971Qa39h-1ktGX3ouStS-akgZIzVc8I
}

const dateAdded = document.getElementById('dateAdded');
const today = new Date().toISOString().split('T')[0];
dateAdded.value = today;
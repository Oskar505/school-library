console.log('ok')


const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        console.log(entry);

        if (entry.isIntersecting) {
            if (entry.target.id == 'indexHeader') {
                entry.target.classList.add('showHeader');
            }

            else if (entry.target.id == 'indexMain') {
                entry.target.classList.add('showMain');
            }
        }
    })
})


const hiddenEl = document.getElementsByClassName('hidden');
console.log(hiddenEl);
console.log(Array.isArray(hiddenEl));


Array.from(hiddenEl).forEach((el) => observer.observe(el));





















// /*
// const header = document.getElementById('indexHeader');
// let headerVisible;

// const headerObserver = new IntersectionObserver(entries => {
//     console.log(entries);
//     headerVisible = entries[0]['isIntersecting'];
//     headerVisiblePart = entries[0]['intersectionRatio']
// })

// headerObserver.observe(header);
// */



// let searchObserverElement = document.getElementById('searchSectionObserver');
// let searchVisible = false;

// let searchObserver = new IntersectionObserver(entries => {
//     searchVisible = true;
// });



// let headerVisible = true;

// // Přidání posluchače události scrollování na HTML element body
// document.addEventListener('wheel', function (event) {
//     console.log('scroll');
//     //console.log(headerVisible);
//     //console.log(headerVisiblePart)
    

    

//     console.log(searchVisible);



//     if (event.deltaY > 0 && headerVisible == true) {
//         headerVisible = false;

//         console.log('scroll down');

//         event.preventDefault();

        
//         // window.scrollTo({
//         //     top: 1080,
//         //     behavior: 'smooth'
//         // });
//     }

//     else if (event.deltaY < 0 && headerVisible == false) {
//         headerVisible = true;
        
//         console.log('scroll up');
//         console.log(event.deltaY);

//         event.preventDefault();

        
//         // window.scrollTo({
//         //     top: 0,
//         //     behavior: 'smooth'
//         // });
//     }


//     // prevent default when

//     else if (headerVisible) {
//         //event.preventDefault();
//     }


//     else if (searchVisible) {

//     }


//     // is search visible
//     searchVisible = false;
//     searchObserver.observe(searchObserverElement);

    
// }, { passive: false });
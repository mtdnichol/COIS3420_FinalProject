"use strict";

//https://www.youtube.com/watch?v=gLWIYk0Sd38 Modal source
window.addEventListener('DOMContentLoaded', () => {
    const markCompleteButtons = document.querySelectorAll("button[name=markItem]");

    for (const button of markCompleteButtons) {
        button.addEventListener('click', event => { //Event listener for paragraph on click
            button.parentElement.parentElement.style.backgroundColor = "#01D27F";
        });
    }
});

// *****
// EDIT LIST FUNCTION

// ran on click of edit title button
function titleSwap(){
    // swap to text input, allowing them to input new title
    document.querySelector(".titleHeader").classList.toggle("hidden");
    document.querySelector(".titleEdit").classList.toggle("hidden");

    let prevTitle = document.querySelector(".titleHeader h2").textContent;

    // set previous title as placeholder of current input
    document.querySelector(".titleEdit input").placeholder = prevTitle;

    return false;
}

// on submit button for title swap
document.querySelector("#titleSubmit").addEventListener("click", function(){
    // grab text from input
    console.log(document.querySelector(".titleEdit input").value);
    let newTitle = document.querySelector(".titleEdit input").value;
    console.log(newTitle);

    // update with database -- ajax call to php script?

    // update title in page
    document.querySelector(".titleHeader h2").textContent = newTitle;

    // swap back headers
    document.querySelector(".titleEdit").classList.toggle("hidden");
    document.querySelector(".titleHeader").classList.toggle("hidden");
});

// *****

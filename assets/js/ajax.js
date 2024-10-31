//state of load more button (by default it's unclicked)
state = 'unclicked'
MaxElement = 1;
window.loader = null;

// on document ready in pure JS
document.addEventListener("DOMContentLoaded", function () {
  let id = setInterval(() => {
    // If $ is defined call init and cancel interval.
    if (typeof $ !== "undefined") {
      init();
      clearInterval(id);
    }
  }, 100);
});

//function to load more content in the parots ids
function hide_more(btn,btnSeeMore,btnSeeLess,elements, maxValue){
  //if its morethan the max value we hide the supplus and show the button
  if(elements.length > maxValue){
    for (let i = maxValue; i < elements.length; i++) {
      const element = elements[i];
      element.style.display='none'
    }
    btn.style.display='block'
    btnSeeMore.style.display='block'
    btnSeeLess.style.display='none'
    state ='unclicked';

  }

  
}
//when the see more buton is clicked
function see_more(btnE='seeMoreBtn',elementsClass='.friend', maxValue=MaxElement){
  //if the function has already been clicked we return the hide function
  //select the button and the elements
  btnSeeMore = document.getElementById('seeMoreSpan')
  btnSeeLess = document.getElementById('seeLessSpan')
  elements = $('.friend')
  if(state =='clicked') return hide_more(btn,btnSeeMore,btnSeeLess,elements,maxValue);
  for (let i = maxValue; i < elements.length; i++) {
    const element = elements[i];
    element.style.display='block'
  }
  btnSeeMore.style.display='none'
  btnSeeLess.style.display='block'
  state ='clicked';
};


function init() {
  $(".ajax .mform").submit(function (e) {
    e.preventDefault();
    handle_submission(this);
  });
  console.log("ajax form loaded");

  $(".ajax #received_invitations .mform input[type='submit']").on(
    "click",
    function (e) {
      e.preventDefault();
      $form = $(this).closest(".mform");
      $form[0].accept.value = $(this).attr("name") == "validate" ? "true" : "";
      handle_submission($form[0]);
    }
  );

  // show loader on click on submit button
  $(".ajax .mform input[type='submit']").click(function (e) {
    console.log("clicked");
    $(window.loader).show(0);
    // alert("clicked");
  });
  console.log($(".ajax .mform input[type='submit']"));

  $.getScript(
    "/local/parrot_social/assets/js/lightbox.min.js",
    function name() {
      $(document).on("click", '[data-toggle="lightbox"]', function (event) {
        event.preventDefault();
        $(this).ekkoLightbox();
      });
      $("#blocker").remove();
      console.log("lightbox setup");
    }
  );

  window.loader = $("#loader").removeClass("d-none").hide();

  //hide friends if more than maximun
  //btn = $('#seeMoreBtn')
  btn = document.getElementById('seeMoreBtn')
  btnSeeMore = document.getElementById('seeMoreSpan')
  btnSeeLess = document.getElementById('seeLessSpan')
  elements = $('.friend')
  hide_more(btn,btnSeeMore,btnSeeLess,elements,MaxElement);

  //hide media if more than maximun
  if(document.getElementById('mediaPage') == null){
    btn1 = document.getElementById('seeMoreMediaBtn')
    elements1 = $('.mediaElements')
    hide_more(btn1,btnSeeMore,btnSeeLess,elements1,MaxElement);  
  }
  
}

function ajaxSubmit(form, successCallback) {
  formData = new FormData(form);
  xhr = new XMLHttpRequest();

  xhr.open("POST", form.getAttribute("action"), true);
  xhr.onreadystatechange = function (data) {
    // Check if the request is finished
    if (xhr.readyState === 4) {
      // Check if the request was successful
      if (xhr.status === 200) {
        successCallback({
          data,
          form,
        });
      } else {
        alert(
          "An error occurred during this operation: " +
            xhr.status +
            " " +
            xhr.statusText
        );
      }
      $(window.loader).hide();
    }
  };
  xhr.send(formData);
}

window.handle_submission = function (form) {
  // $(window.loader).show();
  console.log($(window.loader));
  successCallback = form.getAttribute("callback");
  successCallback = successCallback
    ? window[successCallback]
    : (data) => console.log(data);
  return ajaxSubmit(form, successCallback);
};
window.handle_submission = handle_submission;

function parrotUpdatedCallback(data) {
  console.log(data);
  response = JSON.parse(data.data.target.responseText);
  parrot = response.parrot;
  let imageElement = $(data.form).parent().prev().find(".parrot-image").first();
  let nameElement = imageElement.next();
  if (parrot.picture_url) {
    imageElement.replaceWith(
      `<img 
      class="img-fluid rounded-circle parrot-image border" 
      src="${parrot.picture_url}" 
      alt="The best" 
      style="max-width: 10rem ; ">`
    );
  } else {
    $("<div></div>").load(
      "/local/parrot_social/templates/components/default_parrot_photo.mustache",
      function (image) {
        imageElement.replaceWith(image);
      }
    );
  }

  nameElement
    .html(parrot.name)
    .next()
    .html(parrot.description)
    .closest(".parrot_profile")
    .find(".collapse")
    .collapse("toggle");
}

function postCreatedCallback(data) {
  response = JSON.parse(data.data.target.responseText);
  window.location.reload();
}

function postUpdatedCallback(data) {
  console.log(data);
  response = JSON.parse(data.data.target.responseText);
  post = response.post;
  $(data.form)
    .parent()
    .prev()
    .html(post.text)
    .prev()
    .find("span")
    .html(post.title)
    .parent()
    .parent()
    .find(".collapse")
    .collapse("toggle");
}

function postLikedCallback(data) {
  response = JSON.parse(data.data.target.responseText);
  b = $(data.form).closest(".dropdown").next().find("b");
  let count = (parseInt(b.text()) || 0) + (response.liked ? 1 : -1);
  b.text(count || "")
    .parent()
    .prev()
    .find(".parrot_select")
    .removeClass("no_like")
    .addClass(count > 0 ? "" : "no_like");
}

function postDeletedCallback(data) {
  response = JSON.parse(data.data.target.responseText);
  $(data.form)
    .closest(".modal")
    .on("hidden.bs.modal", function (e) {
      $(data.form).closest(".post").remove();
    })
    .modal("hide");
}

function mediaEditedCallback(data) {
  response = JSON.parse(data.data.target.responseText);
  console.log(response);
  let imagesHtml = response.images.reduce(
    (acc, image) =>
      acc +
      `
        <a data-toggle="lightbox" data-gallery="gallery" href="${image.url}">
          <img class="p-1" src="${image.url}" width="100" height="100">
        </a>
      `,
    ""
  );
  $(data.form)
    .parent()
    .prev()
    .removeClass("d-none")
    .html(imagesHtml)
    .parent()
    .find(".collapse")
    .collapse("toggle");
}

function invitationCreatedCallback(data) {
  console.log(data);
  response = JSON.parse(data.data.target.responseText);
  invitation = response.invitation;
  $menu = $(data.form).closest(".dropdown-menu");
  $(data.form).parent().next().remove().addBack().remove();

  console.log($menu);
  console.log($menu.children());
  // if dropdown-menu is empty, remove dropdown
  if ($menu.children().length == 0) {
    $menu.closest(".dropdown").remove();
  }
}

function invitationUpdatedCallback(data) {
  console.log(data);
  response = JSON.parse(data.data.target.responseText);
  invitation = response.invitation;
  $(data.form).parent().remove();
}

function friendshipCancelled(data) {
  console.log(data);
  debugger;
  response = JSON.parse(data.data.target.responseText);
  $(data.form).closest(".friend").remove();
}

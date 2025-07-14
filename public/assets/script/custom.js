function setupSuggestion(inputId, divId) {
  const searchInput = document.getElementById(inputId);
  const hiddenDiv = document.getElementById(divId);

  searchInput.addEventListener('input', function () {
      if (this.value.length > 0) {
          hiddenDiv.classList.add('expand-height');
      } else {
          hiddenDiv.classList.remove('expand-height');
      }
  });

  document.addEventListener('click', function (event) {
      if (!searchInput.contains(event.target) && !hiddenDiv.contains(event.target)) {
          hiddenDiv.classList.remove('expand-height');
      }
  });
}

setupSuggestion('searchInput', 'hiddenDiv');
setupSuggestion('searchInput2', 'hiddenDiv2');


        // Dropdown functionality
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownButton = document.getElementById('dropdownButton');
        const dropdownMenu = document.getElementById('dropdownMenu');

        if (dropdownButton && dropdownMenu) {
            dropdownButton.addEventListener('click', function() {
                dropdownMenu.classList.toggle('hidden');
            });

            document.addEventListener('click', function(event) {
                if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                    dropdownMenu.classList.add('hidden');
                }
            });
        }
    });
      // Modal functionality
      document.addEventListener("DOMContentLoaded", function() {
          // Handle desktop button (ID)
          const desktopButton = document.getElementById("openModalBtn");
          if (desktopButton) {
              desktopButton.addEventListener("click", function() {
                  document.getElementById("modal").classList.remove("hidden");
              });
          }

          // Handle mobile button (class)
          const mobileButton = document.querySelector(".openModalBtn");
          if (mobileButton) {
              mobileButton.addEventListener("click", function() {
                  document.getElementById("modal").classList.remove("hidden");
              });
          }

          // Handle modal close functionality
          const modal = document.getElementById("modal");
          if (modal) {
              modal.addEventListener("click", function(event) {
                  if (event.target === this || event.target.classList.contains("close")) {
                      this.classList.add("hidden");
                  }
              });
          }

          // Handle close button
          const closeButton = document.getElementById("closeModalBtn");
          if (closeButton) {
              closeButton.addEventListener("click", function() {
                  document.getElementById("modal").classList.add("hidden");
              });
          }
      });

$(document).ready(function () {
    $('.my-select').select2({
        placeholder: 'برای سوال خود برچسب وارد کنید...',
        dir: 'rtl'

    });

    // Initialize CKEditor
    if (typeof ClassicEditor !== 'undefined') {
        ClassicEditor
            .create(document.querySelector('#editor'))
            .then(editor => {
                console.log('Editor was initialized', editor);
            })
            .catch(error => {
                console.error('Error during initialization of the editor', error);
            });
    }

    function updateTags() {
        var selectedOptions = $('.my-select').val();
        var tagsContainer = $('.tags-container');
        tagsContainer.empty();
        if (selectedOptions) {
            selectedOptions.forEach(function (value) {
                var text = $('.my-select option[value="' + value + '"]').text();
                var tag = $('<span class="tag">' + text + '<span class="remove-tag" data-value="' + value + '">×</span></span>');
                tagsContainer.append(tag);
            });
        }
    }

    $('.my-select').on('change', function () {
        updateTags();
    });

    $('body').on('click', '.remove-tag', function () {
        var value = $(this).data('value');
        var selectedOptions = $('.my-select').val();
        selectedOptions = selectedOptions.filter(function (option) {
            return option !== value.toString();
        });
        $('.my-select').val(selectedOptions).trigger('change');
    });

    updateTags();
});


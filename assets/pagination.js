// File: pagination.js
jQuery(document).ready(function ($) {
  let moreUrlPeramVar = String(
    pagimore_ajax_data.pagimore_more_url_param || "more"
  );

  // escape a string for use in RegExp constructor
  function escapeRegExp(str) {
    return String(str).replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  }

  // return path segments as array (no empty strings)
  function getPathSegments() {
    return window.location.pathname
      .replace(/\/+$/, "")
      .split("/")
      .filter(Boolean);
  }

  // find the "more" number from URL (returns integer or null)
  function getMoreNumberFromPath() {
    const segs = getPathSegments();
    const idx = segs.indexOf(moreUrlPeramVar);
    if (idx !== -1 && segs[idx + 1]) {
      const n = parseInt(segs[idx + 1], 10);
      return Number.isFinite(n) ? n : null;
    }
    return null;
  }

  // Function to check for /more/ parameter in URL and extract page numbers
  function checkMoreUrl() {
    // Example URL formats:
    // /page/4/more/2/
    // /page/5/more/3
    const match = window.location.pathname.match(
      new RegExp(`/page/(\\d+)/${moreUrlPeramVar}/(\\d+)/?`)
    );

    if (match) {
      return {
        hasMore: true,
        targetPage: parseInt(match[1], 10), // The final page (e.g., 4)
        startPage: parseInt(match[2], 10), // The starting page (e.g., 2)
      };
    }

    return {
      hasMore: false,
      targetPage: null,
      startPage: null,
    };
  }

  const catBase = pagimore_ajax_data.cat_base || null;

  const wooCatBase = pagimore_ajax_data.woo_cat_base || null;

  let postCatSlug = pagimore_ajax_data.current_cat || null;

  let productTag = pagimore_ajax_data.product_tag || null;

  let postTag = pagimore_ajax_data.zapisi_tag || null;

  let brandSlug = pagimore_ajax_data.woo_brand_slug || null;

  let currentSlug = pagimore_ajax_data.product_cat || null;

  // Norma slash

  // Check if DOM is already loaded
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", normalizeUrlWithSlash);
  } else {
    // DOM is already ready, run immediately
    normalizeUrlWithSlash();
  }

  function normalizeUrlWithSlash() {
    console.log("Running normalizeUrlWithSlash");
    const path = window.location.pathname;

    if (path.includes("/page/") && !path.endsWith("/")) {
      const newUrl = path + "/" + window.location.search + window.location.hash;
      window.history.replaceState(null, "", newUrl);
      console.log("Added trailing slash to pagination URL");
    }
  }

  const moreInfo = checkMoreUrl();

  // Function to check if it is /more/ parameter in URL on page load, if true, load related posts on the page starting from the "more number" page
  // Also works on other page reload processes
  function secondHelper(updateLink = true) {
    function parseMoreUrl() {
      const match = window.location.pathname.match(
        new RegExp(`/page/(\\d+)/${moreUrlPeramVar}/(\\d+)/?`)
      );
      return match
        ? {
            targetPage: parseInt(match[1], 10),
            startPage: parseInt(match[2], 10),
          }
        : null;
    }

    function getCurrentPage() {
      const moreParams = parseMoreUrl();
      return moreParams
        ? moreParams.targetPage
        : parseInt(window.location.pathname.match(/\/page\/(\d+)/)?.[1], 10) ||
            1;
    }

    let currentPage = getCurrentPage();
    let perPage2 = parseInt(pagimore_ajax_data.per_page, 10);
    let loadedPages = [];
    let isLoading = false;
    let isMoreSequence = false;
    let moreStartPage = null;

    const isMobile = window.matchMedia("(max-width: 767px)").matches;
    const enablePagination =
      pagimore_ajax_data.enable_pagination &&
      (isMobile
        ? pagimore_ajax_data.enable_pagination_mobile
        : pagimore_ajax_data.enable_pagination_pc);
    const enableLoadMore =
      pagimore_ajax_data.enable_load_more &&
      (isMobile
        ? pagimore_ajax_data.enable_load_more_mobile
        : pagimore_ajax_data.enable_load_more_pc);

    // Exit if both pagination and Load More are disabled
    if (!enablePagination && !enableLoadMore) {
      $(".ajax-pagination").remove();
      return;
    }

    function loadPosts2(page, append = false, isLast = false) {
      if (isLoading) return Promise.reject();
      isLoading = true;

      $(".pagination-btn, .load-pagimore")
        .prop("disabled", true)
        .addClass("disabled");

      return new Promise((resolve, reject) => {
        $.ajax({
          url: pagimore_ajax_data.ajax_url,
          type: "POST",
          data: {
            action: "pagimore_load_posts",
            page: page || 1,
            per_page: perPage2,
            accumulated_pages: loadedPages,
            query_args: JSON.stringify(pagimore_ajax_data.query_args || {}),
            append: append ? 1 : 0,
            category_slug: currentSlug,
            woo_tag: productTag,
            cubeb_post_tag: postTag,
            post_cat: postCatSlug,
            category_base: catBase,
            woo_category_base: wooCatBase,
            brand_slug: brandSlug,
            security: pagimore_ajax_data.nonce,
          },
          beforeSend: function () {
            if (!append && !isMoreSequence) {
              $("." + pagimore_ajax_data.query_selector).empty();
            }
            $(".page-loading").addClass("loading");
          },
          success: function (response) {
            $(".page-loading").removeClass("loading");
            $(".page-loading").remove();

            if (response.html) {
              if (append) {
                $("." + pagimore_ajax_data.query_selector).append(
                  response.html
                );
              } else {
                $("." + pagimore_ajax_data.query_selector).html(response.html);
              }

              if (isLast) {
                $(".ajax-pagination").remove();
                $("." + pagimore_ajax_data.query_selector).after(
                  response.pagination
                );
              }
              maxPages = response.max_pages;
              // Update perPage2 from response if changed
              perPage2 = response.posts_per_page || perPage2;
            }
            resolve(response);
          },
          error: function (xhr, status, error) {
            $(".page-loading").removeClass("loading");
            $(".page-loading").remove();

            console.error("AJAX error:", status, error);
            reject(error);
          },
          complete: function () {
            isLoading = false;
            $(".pagination-btn, .load-pagimore")
              .prop("disabled", false)
              .removeClass("disabled");
          },
        });
      });
    }

    function updateUrl2(page, isLoadMore = false) {
      const safe = escapeRegExp(moreUrlPeramVar);
      const tailRe = new RegExp("/page/\\d+/(?:" + safe + "/\\d+)?/?$");
      let basePath = window.location.pathname.replace(tailRe, "");
      if (basePath.endsWith("/")) basePath = basePath.slice(0, -1);

      let url = `${basePath}/page/${page}/`;

      if (isMoreSequence && moreStartPage !== null) {
        url += `${moreUrlPeramVar}/${moreStartPage}/`;
      } else if (isLoadMore && moreStartPage !== null) {
        url += `${moreUrlPeramVar}/${moreStartPage}/`;
      }

      window.history.replaceState({ page }, "", url);
    }

    async function initialize() {
      const moreParams = parseMoreUrl();

      if (moreParams) {
        isMoreSequence = true;
        moreStartPage = moreParams.startPage;
        currentPage = moreParams.targetPage || 1;
        if (updateLink) {
          if (!pagimore_ajax_data.remove_pages) {
            updateUrl2(currentPage);
          }
        }

        $("." + pagimore_ajax_data.query_selector).empty();

        for (
          let page = moreParams.startPage;
          page <= moreParams.targetPage;
          page++
        ) {
          const append = page !== moreParams.startPage;
          const isLast = page === moreParams.targetPage;
          await loadPosts2(page, append, isLast);
          loadedPages.push(page);
        }
      } else {
        isMoreSequence = false;
        moreStartPage = null;
        await loadPosts2(currentPage, false, true);
        loadedPages = [currentPage];
        if (updateLink) {
          if (!pagimore_ajax_data.remove_pages) {
            updateUrl2(currentPage);
          }
        }
      }
    }

    initialize();

    window.addEventListener("popstate", function () {
      initialize();
    });
  }
  if (moreInfo.hasMore) {
    secondHelper(true);
  }
  // Main pagination and Load More functionality section
  function getCurrentPageFromUrl() {
    const match = window.location.pathname.match(/\/page\/(\d+)/);
    if (match && match[1]) {
      return parseInt(match[1], 10);
    }
    return 1;
  }
  console.log("The page is", getCurrentPageFromUrl());
  let currentPage = getCurrentPageFromUrl();
  let perPage = parseInt(pagimore_ajax_data.per_page, 10);
  let loadedPages = [currentPage];
  window.maxPages = pagimore_ajax_data.max_pages;
  let isLoading = false; // Track loading state

  // Determine device type based on screen width
  const isMobile = window.matchMedia("(max-width: 767px)").matches;
  const enablePagination =
    pagimore_ajax_data.enable_pagination &&
    (isMobile
      ? pagimore_ajax_data.enable_pagination_mobile
      : pagimore_ajax_data.enable_pagination_pc);
  const enableLoadMore =
    pagimore_ajax_data.enable_load_more &&
    (isMobile
      ? pagimore_ajax_data.enable_load_more_mobile
      : pagimore_ajax_data.enable_load_more_pc);

  // Exit if both pagination and Load More are disabled
  if (!enablePagination && !enableLoadMore) {
    $(".ajax-pagination").remove();
    return;
  }

  function loadPosts(page, append = false, postsPerPage = perPage) {
    if (isLoading) return; // Prevent new requests
    isLoading = true;

    $(".pagination-btn, .load-pagimore")
      .prop("disabled", true)
      .addClass("disabled");

    // Return the promise so click handlers can wait on it
    return $.ajax({
      url: pagimore_ajax_data.ajax_url,
      type: "POST",
      data: {
        action: "pagimore_load_posts",
        page: page || 1,
        per_page: postsPerPage,
        accumulated_pages: loadedPages,
        query_args: JSON.stringify(pagimore_ajax_data.query_args || {}),
        append: append ? 1 : 0,
        category_slug: currentSlug,
        woo_tag: productTag,
        cubeb_post_tag: postTag,
        post_cat: postCatSlug,
        category_base: catBase,
        woo_category_base: wooCatBase,
        brand_slug: brandSlug,
        security: pagimore_ajax_data.nonce,
      },
      beforeSend: function () {
        $(".page-loading").addClass("loading");
        if (!append) {
          $("." + pagimore_ajax_data.query_selector).empty();
        }
      },
      success: function (response) {
        $(".page-loading").removeClass("loading");
        $(".page-loading").remove();
        console.log("loadPosts", postTag);

        if (response.html) {
          if (!append) {
            // Full replace
            $("." + pagimore_ajax_data.query_selector).html(response.html);
            loadedPages = [page];
          } else {
            // Append mode
            $("." + pagimore_ajax_data.query_selector).append(response.html);
            if (!loadedPages.includes(page)) {
              loadedPages.push(page);
            }
          }
        }

        // Replace pagination markup
        if (response.pagination) {
          $(".ajax-pagination").replaceWith(response.pagination);
        }

        // Update maxPages if provided
        if (typeof response.max_pages !== "undefined") {
          window.maxPages = response.max_pages;
        }

        // Update currentPage and perPage
        currentPage = page;
        const postsPer = $(".ajax-pagination").data("posts-per-page");
        if (postsPer) {
          perPage = parseInt(postsPer, 10);
        }

        console.log("Loaded pages:", loadedPages);
        console.log("Current page:", currentPage);
        console.log("Max pages:", window.maxPages);
      },
      error: function (xhr, status, error) {
        $(".page-loading").removeClass("loading");
        $(".page-loading").remove();

        console.error("AJAX error:", status, error);
      },
      complete: function () {
        isLoading = false;
        $(".pagination-btn, .load-pagimore")
          .prop("disabled", false)
          .removeClass("disabled");
      },
    });
  }

  function updateUrl(page, isLoadMore = false, loadMoreStartPage = null) {
    const safe = escapeRegExp(moreUrlPeramVar);
    const tailRe = new RegExp("/page/\\d+/(?:" + safe + "/\\d+)?/?$");
    let basePath = window.location.pathname.replace(tailRe, "");

    if (basePath.endsWith("/")) {
      basePath = basePath.slice(0, -1);
    }
    let url = `${basePath}/page/${page}/`;
    if (isLoadMore) {
      url +=
        loadMoreStartPage !== null
          ? `${moreUrlPeramVar}/${loadMoreStartPage}/`
          : `${moreUrlPeramVar}/`;
    }
    window.history.pushState({ page: page }, "", url);
  }

  let loadMoreOriginPage = null;

  if (enablePagination) {
    $(document).on("click", ".pagination-btn", function () {
      let page = $(this).data("page");
      let pperPage = parseInt(pagimore_ajax_data.per_page, 10);
      currentPage = page;
      loadedPages = [page];
      loadMoreOriginPage = page;

      loadPosts(page, false, pperPage);
      if (!pagimore_ajax_data.remove_pages) {
        updateUrl(page, false);
      }
    });
  }

  if (enableLoadMore) {
    $(document).on("click", ".load-pagimore", function () {
      if (!enableLoadMore) return;

      // ensure loadedPages has at least currentPage
      if (!Array.isArray(loadedPages) || loadedPages.length === 0)
        loadedPages = [currentPage];

      const nextPage = Math.max(...loadedPages, currentPage) + 1;
      const pperPage = parseInt(pagimore_ajax_data.per_page, 10) || perPage;

      // decide loadMoreOriginPage:
      // priority:
      // 1) explicit "more" in URL (e.g. /page/4/more/2)
      // 2) previously stored loadMoreOriginPage (if set)
      // 3) fallback to currentPage
      const urlMoreNumber = getMoreNumberFromPath();

      if (loadMoreOriginPage === null) {
        if (urlMoreNumber !== null) loadMoreOriginPage = urlMoreNumber;
        else loadMoreOriginPage = currentPage;
      }

      // call loadPosts — prefer it to return a Promise (see note)
      const maybePromise = loadPosts(nextPage, true, pperPage);

      // update UI / URL after load completes if promise returned
      if (maybePromise && typeof maybePromise.then === "function") {
        maybePromise
          .then(() => {
            // push to loaded pages and update URL
            if (!loadedPages.includes(nextPage)) loadedPages.push(nextPage);
            currentPage = nextPage;
            if (!pagimore_ajax_data.remove_pages) {
              updateUrl(nextPage, true, loadMoreOriginPage);
            }
          })
          .catch((e) => {
            console.error("Load more AJAX failed:", e);
          });
      } else {
        // fallback: if loadPosts doesn't return a promise, do optimistic update
        if (!loadedPages.includes(nextPage)) loadedPages.push(nextPage);
        currentPage = nextPage;
        if (!pagimore_ajax_data.remove_pages) {
          updateUrl(nextPage, true, loadMoreOriginPage);
        }
      }

      // debug
      console.log(
        "nextPage",
        nextPage,
        "loadMoreOriginPage",
        loadMoreOriginPage,
        "currentPage",
        currentPage
      );
    });
  }
});

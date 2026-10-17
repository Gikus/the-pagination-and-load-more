// File: pagination.js
jQuery(document).ready(function ($) {
  let moreUrlPeramVar = String(
    cubeabf_ajax_data.cubeabf_more_url_param || "after",
  );
  window.cubeabfActiveRanges = {};

  function filterKeyExists(key) {
    return (
      $(`.cubeabf-filter[data-filter-name="${CSS.escape(key)}"]`).length > 0
    );
  }

  function filterValueExists(key, value) {
    const $wrap = $(`.cubeabf-filter[data-filter-name="${CSS.escape(key)}"]`);
    if (!$wrap.length) return false;
    return (
      $wrap.find(`.cubeabf-filter-input[value="${CSS.escape(value)}"]`).length >
      0
    );
  }

  /**
   * Try to split a segment like:
   *   pa_super-nov-pal-retro-pedal
   * into:
   *   key = pa_super-nov-pal
   *   value = retro-pedal
   *
   * using DOM existence checks.
   */
  function splitByExistingInput(segment, prefix) {
    // segment starts with prefix already (e.g. "pa_")
    // find all dash positions to try splits
    const dashPositions = [];
    for (let i = 0; i < segment.length; i++) {
      if (segment[i] === "-") dashPositions.push(i);
    }

    // Try splits from RIGHT to LEFT, so we keep the VALUE as long as possible
    for (let p = dashPositions.length - 1; p >= 0; p--) {
      const idx = dashPositions[p];
      const key = segment.substring(0, idx);
      const value = segment.substring(idx + 1);

      if (!key.startsWith(prefix)) continue;
      if (!value) continue;

      // Key must exist in DOM, and value should exist in that filter
      if (
        filterKeyExists(key) &&
        filterValueExists(key, decodeURIComponent(value))
      ) {
        return { key, value };
      }
    }

    // If we can't validate value, at least validate key (still better than lastDash)
    for (let p = dashPositions.length - 1; p >= 0; p--) {
      const idx = dashPositions[p];
      const key = segment.substring(0, idx);
      const value = segment.substring(idx + 1);
      if (!key.startsWith(prefix)) continue;
      if (filterKeyExists(key)) {
        return { key, value };
      }
    }

    return null;
  }

  function getOneStepLessFromInput($input) {
    const value = parseFloat($input.val());

    if (isNaN(value)) return value;

    // Уменьшаем число в два раза
    const halvedValue = parseFloat((value / 2).toFixed(10));

    // Если результат меньше 1, возвращаем 1, иначе возвращаем результат деления
    return halvedValue < 1 ? 1 : halvedValue;
  }

  // Show skeleton loader
  function showSkeletonLoader() {
    const $container = $("." + cubeabf_ajax_data.qselect);

    if ($container.length === 0) {
      console.warn("Container not found: ", cubeabf_ajax_data.qselect);
      return;
    }

    let pperPage = cubeabf_ajax_data.pppage || 6;

    for (let i = 0; i < pperPage; i++) {
      $container.append(`
            <div class="skeleton-item">
                <div class="skeleton-img"></div>
                <div class="skeleton-title"></div>
                <div class="skeleton-text"></div>
                <div class="skeleton-text"></div>
            </div>
        `);
    }
  }

  // Remove skeleton loader
  function hideSkeletonLoader() {
    const $container = $("." + cubeabf_ajax_data.qselect);
    $container.find(".skeleton-item").fadeOut(300, function () {
      $(this).remove();
    });
  }

  function toggleSkeletonOpacity() {
    const hasSkeleton = $(".skeleton-item").length > 0;
    $(".ready.loading").toggleClass("has-skeleton", hasSkeleton);
  }

  function clampRangeValue(value, step = 0.01) {
    const v = parseFloat(value);
    if (isNaN(v)) return 0;

    return Math.round(v / step) * step;
  }
  window.cubeabfActiveRanges = window.cubeabfActiveRanges || {};

  const skelloader = cubeabf_ajax_data.skeleton_loader;

  // Disable filters some time after load

  $(".cubeabf-filter").css("pointer-events", "none").prop("disabled", true);

  setTimeout(function () {
    $(".cubeabf-filter").css("pointer-events", "").prop("disabled", false);
  }, 1000);

  function cubeabfGetConfigEl() {
    return $(".cubeabf-config").first(); // must exist
  }

  const $cfg = $(".cubeabf-config").first();
  if (!$cfg.length) {
    console.error(
      "Pagimore: missing .cubeabf-config element. Settings cannot be read.",
    );
  }

  function collectTaxSettings() {
    const $cfg = cubeabfGetConfigEl();

    const relRaw =
      $cfg.data("taxRelation") ?? $cfg.attr("data-tax-relation") ?? "AND";
    const rel = String(relRaw).toUpperCase();

    const ops = {};
    $(".cubeabf-filter[data-filter-type='taxonomy']").each(function () {
      const tax = String($(this).data("filter-name") || "");
      if (!tax) return;

      const opRaw =
        $(this).data("taxOperator") ??
        $(this).attr("data-tax-operator") ??
        "IN";
      const op = String(opRaw).toUpperCase();

      ops[tax] = op === "AND" ? "AND" : "IN";
    });

    return { tax_relation: rel === "OR" ? "OR" : "AND", tax_operators: ops };
  }

  function collectMetaSettings() {
    const $cfg = cubeabfGetConfigEl();

    const modeRaw =
      $cfg.data("ratingMode") ?? $cfg.attr("data-rating-mode") ?? "bucket";
    const relRaw =
      $cfg.data("ratingMetaRelation") ??
      $cfg.attr("data-rating-meta-relation") ??
      "OR";

    const mode = String(modeRaw).toLowerCase();
    const rel = String(relRaw).toUpperCase();

    return {
      rating_mode: mode === "min" ? "min" : "bucket",
      rating_meta_relation: rel === "AND" ? "AND" : "OR",
    };
  }

  let initurl = true;
  $(".cubeabf-filter").click(function () {
    currentPage = 1;
    initurl = false;
  });
  const loadBtn = cubeabf_ajax_data.load_btn;

  let rangeCanUpdateUrl = false;
  let postsCountMayShowUp = false;
  let chipsCanUpdate = false;
  $(".cubeabf-load-posts").click(function () {
    currentPage = 1;
    initurl = false;
    rangeCanUpdateUrl = true;
    postsCountMayShowUp = true;
    chipsCanUpdate = true;
  });

  $(".cubeabf-filter[data-filter-name$='_range']").on(
    "pointerdown",
    function () {
      currentPage = 1;
      initurl = false;
    },
  );

  const eventType = loadBtn === "true" ? "click" : "input change";
  const selector =
    loadBtn === "true" ? ".cubeabf-load-posts" : ".cubeabf-filter";

  window.cubeabfIsDraggingRange = false;
  let typingTimer;

  let dragging = null;
  let $activeSlider = null;

  let lastMove = 0;

  // Outside handler
  let suppressTextInputHandler = false;

  // Функция теперь просто проверяет, есть ли вообще какой-либо range-фильтр в URL
  function hasAnyRangeInUrl() {
    return /_range=[\d.]+-[\d.]+/i.test(window.location.pathname);
  }

  const updateurl = cubeabf_ajax_data.updateurl || "true";
  // Глобальный флаг защиты на самом верху файла
  window.cubeabfIsUpdatingLimits = false;
  window.cubeabfRangeValues = {};

  const isRepeaterRoot = String(
    $(".cubeabf-config").first().data("rep-roots") || "",
  )
    .split(",")
    .map((s) => s.trim())
    .filter(Boolean);

  window.cubeabfOriginalRanges = {};

  const curP = $(`.${cubeabf_ajax_data.wrapperCss}`).data("current-page");
  const pageCountUp = $(".cubeabf-current-page");
  pageCountUp.text(curP);

  const pageW = cubeabf_ajax_data.page.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

  document.title = document.title
    // Remove "... - Page 3"
    .replace(new RegExp(`\\s*[-–—|]\\s*${pageW}\\s+\\d+`, "ig"), "")
    // Remove "Page 3 – ..."
    .replace(new RegExp(`^${pageW}\\s+\\d+\\s*[-–—|]\\s*`, "i"), "")
    .trim();

  if (curP > 1) {
    document.title += ` - ${cubeabf_ajax_data.page} ${curP}`;
  }

  $(document).ajaxStop(function () {
    setTimeout(() => {
      // 1. Get the wrapper element and force clear its jQuery data cache
      const $wrapper = $(`.${cubeabf_ajax_data.wrapperCss}`).last();
      $wrapper.removeData("current-page");

      // 2. Read the live attribute directly from the DOM using .attr()
      const currP = $wrapper.attr("data-current-page");

      // 3. Check if the element itself is missing or has no inner HTML inside it
      const isHtmlEmpty =
        $wrapper.length === 0 || $.trim($wrapper.html()) === "";

      const pageCountUp = $(".cubeabf-current-page");

      const pageWord = cubeabf_ajax_data.page.replace(
        /[.*+?^${}()|[\]\\]/g,
        "\\$&",
      );

      document.title = document.title
        // Remove "... - Page 3"
        .replace(new RegExp(`\\s*[-–—|]\\s*${pageWord}\\s+\\d+`, "ig"), "")
        // Remove "Page 3 – ..."
        .replace(new RegExp(`^${pageWord}\\s+\\d+\\s*[-–—|]\\s*`, "i"), "")
        .trim();

      if (currP > 1) {
        document.title += ` - ${cubeabf_ajax_data.page} ${currP}`;
      }

      $(document).on("click", ".cubeabf-reset-filters", function (e) {
        e.preventDefault();
        document.title = document.title
          .replace(new RegExp(`\\s*[-|]\\s*${pageWord}\\s+\\d+`, "i"), "")
          .trim();
      });

      // 4. If HTML is empty, attribute is missing, or equals zero, reset to 1
      if (
        isHtmlEmpty ||
        typeof currP === "undefined" ||
        currP === null ||
        currP === "" ||
        currP === "0" ||
        currP === 0
      ) {
        pageCountUp.text(1);
      } else {
        pageCountUp.text(currP);
      }
    }, 400);
  });

  // Total pages

  // 1. Initial check on page load
  const totalPInitial = $(`.${cubeabf_ajax_data.wrapperCss}`).attr(
    "data-total-pages",
  );
  const totalCountUpInitial = $(".cubeabf-update-pages-count");

  const currentPostsCount = $(".cubeabf-current-posts-count");

  const $wrapp = $(`.${cubeabf_ajax_data.wrapperCss}`).last();

  // 2. Get the attribute
  const currentPosts = $wrapp.attr("data-total-count");

  if (
    typeof totalPInitial === "undefined" ||
    totalPInitial === null ||
    totalPInitial === "" ||
    totalPInitial === "0" ||
    totalPInitial === 0
  ) {
    totalCountUpInitial.text(1);
    currentPostsCount.text(0);
  } else {
    totalCountUpInitial.text(totalPInitial);
    currentPostsCount.text(currentPosts);
  }

  // 2. Check after AJAX requests finish
  $(document).ajaxStop(function () {
    setTimeout(() => {
      // 1. Force clean jQuery cache and get the wrapper element
      const $wrapper = $(`.${cubeabf_ajax_data.wrapperCss}`).last();

      // 2. Get the attribute
      const totalP = $wrapper.attr("data-total-pages");
      const totalgPages = $(".cubeabf-all-total-pages");
      const globalPostsCount = $(".cubeabf-all-total-posts");
      const dataTotalPostsCount = $wrapper.attr("data-total-count");

      const globalMaxP = $wrapper.attr("data-total-max-pages");
      if (globalMaxP) {
        totalgPages.html(globalMaxP);
      }

      if (dataTotalPostsCount) {
        globalPostsCount.html(dataTotalPostsCount);
      }

      // 3. Check if the element has NO HTML inside it (trimmed to ignore whitespace)
      const isHtmlEmpty =
        $wrapper.length === 0 || $.trim($wrapper.html()) === "";

      const totalCountUp = $(".cubeabf-update-pages-count");

      // 4. If attribute is missing/zero OR the wrapper has no HTML content, set to 1
      if (
        isHtmlEmpty ||
        typeof totalP === "undefined" ||
        totalP === null ||
        totalP === "" ||
        totalP === "0" ||
        totalP === 0
      ) {
        totalCountUp.text(1);
      } else {
        totalCountUp.text(totalP);
      }
    }, 500);
  });

  let cubeabfClicked = false;
  $(document).on("click", ".cubeabf-filter, .cubeabf-chip", function (e) {
    cubeabfClicked = true;
  });

  const updateChips = loadBtn !== "true" || chipsCanUpdate;

  // escape a string for use in RegExp constructor
  function escapeRegExp(str) {
    return String(str).replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  }

  function getPathSegments() {
    return window.location.pathname
      .replace(/\/+$/, "")
      .split("/")
      .filter(Boolean);
  }

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
      new RegExp(`/page/(\\d+)/${moreUrlPeramVar}/(\\d+)/?`),
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

  function getSearchTerm() {
    const url = window.location.href;
    const currentUrl = new URL(url);
    let searchTerm = currentUrl.searchParams.get("s");

    // Handle pretty permalink version like /search/something/
    if (!searchTerm) {
      const match = url.match(/\/search\/([^/]+)/);
      if (match && match[1]) {
        searchTerm = decodeURIComponent(match[1].replace(/-/g, " "));
      }
    }

    return searchTerm || "";
  }

  // Usage:
  const searchParam = getSearchTerm();

  let postCatSlug = cubeabf_ajax_data.current_cat || null;

  let productTag = cubeabf_ajax_data.product_tag || null;

  let postTag = cubeabf_ajax_data.zapisi_tag || null;

  let brandSlug = cubeabf_ajax_data.woo_brand_slug || null;

  let currentSlug = cubeabf_ajax_data.product_cat || null;

  let ptype = cubeabf_ajax_data.ptype;

  let qselect = cubeabf_ajax_data.qselect;

  const wrapclass = cubeabf_ajax_data.wrapclass || "post-list-wrap";

  let parmore = cubeabf_ajax_data.more || "true";

  let order = cubeabf_ajax_data.order || "DESC";
  let orderby = cubeabf_ajax_data.orderby || "post_date";

  const pagiwrapper = `.${cubeabf_ajax_data.wrapperCss}`;
  const pagiloadMore = `.${cubeabf_ajax_data.loadMoreCss}`;
  const pagiloader = `.${cubeabf_ajax_data.loaderCss}`;
  const cubeabfloader = `.${cubeabf_ajax_data.moreloaderCss}`;
  const pagiBtn = `.${cubeabf_ajax_data.pagiBtn}`;
  const pLoader = cubeabf_ajax_data.ploader || "Loading...";
  const lmoretext = cubeabf_ajax_data.lmoretext;
  const ploaderimg = cubeabf_ajax_data.ploaderimg || "false";
  const pageLeftIcon =
    cubeabf_ajax_data.pagelefticon ||
    "/wp-content/plugins/cubeb-pagination-and-load-more/assets/images/slick-arrow-l-active.svg";
  const pageRightIcon =
    cubeabf_ajax_data.pagerighticon ||
    "/wp-content/plugins/cubeb-pagination-and-load-more/assets/images/slick-arrow-r-active.svg";
  const pagelefttext = cubeabf_ajax_data.pagelefttext || "false";
  const pagerighttext = cubeabf_ajax_data.pagerighttext || "false";
  const rightleftempty = cubeabf_ajax_data.rightleftempty || "true";
  const rightlefthide = cubeabf_ajax_data.rightlefthide || "false";
  const firstlast = cubeabf_ajax_data.firstlast || "false";
  const firsttext = cubeabf_ajax_data.firsttext || "false";
  const lasttext = cubeabf_ajax_data.lasttext || "false";
  const firstIcon =
    cubeabf_ajax_data.firsticon ||
    "/wp-content/plugins/cubeb-pagination-and-load-more/assets/images/arrow-double-start.svg";
  const lastIcon =
    cubeabf_ajax_data.lasticon ||
    "/wp-content/plugins/cubeb-pagination-and-load-more/assets/images/arrow-double-end.svg";
  const haslmButton = document.querySelector(pagiloadMore) !== null;
  const $cubeabfloader = $(cubeabfloader);

  const enable_pagination = cubeabf_ajax_data.enable_pagination;
  const enable_load_more = cubeabf_ajax_data.enable_load_more;
  const enable_pagination_mobile = cubeabf_ajax_data.enable_pagination_mobile;
  const enable_load_more_mobile = cubeabf_ajax_data.enable_load_more_mobile;
  const enable_pagination_pc = cubeabf_ajax_data.enable_pagination_pc;
  const enable_load_more_pc = cubeabf_ajax_data.enable_load_more_pc;
  const maxwidthmobile = parseInt(cubeabf_ajax_data.maxwidth || "767");

  const midsizeRaw = String(cubeabf_ajax_data.mid_size || "2,1,767");

  const midsizeParts = midsizeRaw.split(",");

  const midsizeDesktop = parseInt(midsizeParts[0] || "2", 10);
  const midsizeMobile = parseInt(midsizeParts[1] || "1", 10);
  const mobileBp = parseInt(midsizeParts[2] || "767", 10);

  const isBpMobile = window.matchMedia(`(max-width: ${mobileBp}px)`).matches;

  const midSize = isBpMobile ? midsizeMobile : midsizeDesktop;

  const ajaxoff = cubeabf_ajax_data.ajaxoff || "false";

  const arrowsonly = cubeabf_ajax_data.arrowsonly || "false";
  let infinite = cubeabf_ajax_data.infinite || "true";
  const enable_infinite_mobile =
    cubeabf_ajax_data.enable_infinite_mobile || "true";
  const enable_infinite_pc = cubeabf_ajax_data.enable_infinite_pc || "true";
  // Norma slash func
  console.log("Mobile w", maxwidthmobile);
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

  if (!haslmButton && !moreInfo.hasMore && infinite != "true") {
    $cubeabfloader.empty();
  }
  let isLoading = false; // Track loading state
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
  let perPage = cubeabf_ajax_data.pppage || 6;

  let loadedPages = [currentPage];
  window.maxPages = cubeabf_ajax_data.max_pages;

  // Determine device type based on screen width
  const isMobile = window.matchMedia(
    `(max-width: ${maxwidthmobile}px)`,
  ).matches;

  // Use 'true' as default if a property is undefined or falsy
  const enablePagination =
    cubeabf_ajax_data.enable_pagination &&
    (isMobile
      ? cubeabf_ajax_data.enable_pagination_mobile
      : cubeabf_ajax_data.enable_pagination_pc);
  const enableLoadMore =
    cubeabf_ajax_data.enable_load_more &&
    (isMobile
      ? cubeabf_ajax_data.enable_load_more_mobile
      : cubeabf_ajax_data.enable_load_more_pc);

  const enableInfinite =
    cubeabf_ajax_data.infinite &&
    (isMobile
      ? cubeabf_ajax_data.enable_infinite_mobile
      : cubeabf_ajax_data.enable_infinite_pc);

  // Exit if both pagination and Load More are disabled
  if (!enablePagination && !enableLoadMore && !enableInfinite) {
    $(pagiwrapper).remove();
    return;
  }

  function cubeabfGetScopeIO() {
    const el = document.querySelector(".cubeabf-scope-io");
    if (!el || !el.value) return null;
    try {
      return JSON.parse(el.value);
    } catch (e) {
      return null;
    }
  }

  const mode = $(".cubeabf-config").data("filters-mode") || "and";

  const cmode = cubeabf_ajax_data.filters_mode || mode;

  function cubeabfGetScopeConfig() {
    const $cfg = $(".cubeabf-config").first();
    if (!$cfg.length) return { include: "", exclude: "" };

    const include =
      $cfg.data("scopeInclude") ?? $cfg.attr("data-scope-include") ?? "";
    const exclude =
      $cfg.data("scopeExclude") ?? $cfg.attr("data-scope-exclude") ?? "";

    return { include: String(include), exclude: String(exclude) };
  }

  function lockFilters() {
    $(".cubeabf-filter")
      .css("pointer-events", "none")
      .addClass("cubeabf-loading");
  }

  function unlockFilters() {
    $(".cubeabf-filter")
      .css("pointer-events", "")
      .removeClass("cubeabf-loading");
  }

  const noFoundDiv = cubeabf_ajax_data.no_found_infinite;

  const noFoundPosts = cubeabf_ajax_data.no_found_posts;

  function loadPosts(page, append = false, postsPerPage = perPage) {
    if (isLoading) return; // Prevent new requests
    isLoading = true;
    lockFilters();
    const grid = $("." + qselect).closest("." + wrapclass);
    $(pagiBtn + ", " + pagiloadMore)
      .prop("disabled", true)
      .addClass("disabled");
    const customFields = $(".cubeabf-live-search").data("custom-fields") || "";
    // Return the promise so click handlers can wait on it

    const anyRowMap = {};

    $(".cubeabf-filter").each(function () {
      const $anywrap = $(this);

      const dbKey = String($anywrap.attr("data-meta-key") || "");

      const anyRow =
        $anywrap.attr("data-any-row") === "1" ||
        $anywrap.attr("data-any-row") === "true";

      if (dbKey) {
        anyRowMap[dbKey] = anyRow;
      }
    });

    return $.ajax({
      url: cubeabf_ajax_data.ajax_url,
      type: "POST",
      data: {
        action: "cubeabf_load_posts",
        page: page || 1,
        per_page: postsPerPage,
        accumulated_pages: loadedPages,
        query_args: JSON.stringify(cubeabf_ajax_data.query_args || {}),
        append: append ? 1 : 0,
        category_slug: currentSlug,
        woo_tag: productTag,
        cubeb_post_tag: postTag,
        post_cat: postCatSlug,
        brand_slug: brandSlug,
        ptype: ptype,
        qselect: qselect,
        wrapclass: wrapclass,
        lmore: cubeabf_ajax_data.lmore,
        order: order,
        orderby: orderby,
        parmore: parmore,
        ploader: pLoader,
        lmoretext: lmoretext,
        ploaderimg: ploaderimg,
        pagelefticon: pageLeftIcon,
        pagerighticon: pageRightIcon,
        pagelefttext: pagelefttext,
        pagerighttext: pagerighttext,
        rightleftempty: rightleftempty,
        rightlefthide: rightlefthide,
        firstlast: firstlast,
        firsttext: firsttext,
        lasttext: lasttext,
        firsticon: firstIcon,
        lasticon: lastIcon,
        enable_pagination: enable_pagination,
        enable_load_more: enable_load_more,
        enable_pagination_mobile: enable_pagination_mobile,
        enable_load_more_mobile: enable_load_more_mobile,
        enable_pagination_pc: enable_pagination_pc,
        enable_load_more_pc: enable_load_more_pc,
        maxwidth: maxwidthmobile,
        ajaxoff: ajaxoff,
        arrowsonly: arrowsonly,
        infinite: infinite,
        enable_infinite_pc: enable_infinite_pc,
        enable_infinite_mobile: enable_infinite_mobile,
        midsize: midSize,
        mid_desktop: midsizeDesktop,
        mid_mobile: midsizeMobile,
        mobile_bp: mobileBp,
        ...collectTaxSettings(),
        ...collectMetaSettings(),
        scope_io: cubeabfGetScopeIO(),
        scope_config: cubeabfGetScopeConfig(),
        filters_mode: cmode || "and",
        s: searchParam || "",
        current_url: window.location.href,
        custom_fields: customFields,
        repeater_roots: isRepeaterRoot,
        any_row: anyRowMap,
        security: cubeabf_ajax_data.nonce,
      },
      beforeSend: function () {
        $(".ready").addClass("loading");
        toggleSkeletonOpacity();
      },
      success: function (response) {
        if ($(".ready")) {
          $(".ready").removeClass("loading");
        }
        grid.removeClass("load");
        // Remove skeleton
        hideSkeletonLoader();
        const data = response && response.data ? response.data : response;
        const isNoPostsHtml = response.html.includes("no-posts-message");

        if (response.html) {
          if (!append) {
            if ($(".cubeabf-no-results").length !== 0) {
              $(".cubeabf-no-results").remove();
            }

            if (
              $(".cubeabf-meassages").find(".no-posts-message").length !== 0
            ) {
              $(".cubeabf-meassages").find(".no-posts-message").remove();
            }

            if ($(".shortcode-message").length !== 0) {
              $(".shortcode-message").remove();
            }
            // Full replace
            $("." + qselect).html(response.html);
            loadedPages = [page];
          } else {
            if ($(".cubeabf-no-results").length !== 0) {
              $(".cubeabf-no-results").remove();
            }

            if (
              $(".cubeabf-meassages").find(".no-posts-message").length !== 0
            ) {
              $(".cubeabf-meassages").find(".no-posts-message").remove();
            }
            if ($(".shortcode-message").length !== 0) {
              $(".shortcode-message").remove();
            }
            // Append mode
            $("." + qselect).append(response.html);
            if (!loadedPages.includes(page)) {
              loadedPages.push(page);
            }
          }

          window.cubeabfRefreshInfinite = function () {
            setTimeout(() => {
              jQuery(window).trigger("resize.cubeabf");
              jQuery(window).trigger("scroll.cubeabf");
              setTimeout(() => jQuery(window).trigger("scroll.cubeabf"), 100);
            }, 0);
          };
        } else {
          maxPages = response.max_pages;
          infinite = false;

          if (currentPage > maxPages && !response.html && !append) {
            // Guard: only append if message not already present
            if (
              $(".shortcode-message").length === 0 &&
              $(".cubeabf-meassages").find(".no-posts-message").length === 0
            ) {
              if (noFoundPosts !== "false") {
                $(".cubeabf-meassages").append(
                  `<h2 class="no-posts-message js-message">${noFoundPosts}</h2>`,
                );
              }
            }
          } else if (currentPage > maxPages && append) {
            // Guard: only append if message not already present
            if (
              $(".shortcode-message").length === 0 &&
              $(".cubeabf-meassages").find(".no-posts-message").length === 0
            ) {
              if (noFoundDiv !== "false") {
                $(".cubeabf-meassages").append(
                  `<div class="no-posts-message js-infinite-message">${noFoundDiv}</div>`,
                );
              }
            }
          }

          if (!append) {
            // FILTER CHANGE (replace mode): clear old posts
            $("." + qselect).empty();
          }
        }

        // Replace pagination markup
        if (response.pagination && Number(response.max_pages) > 1) {
          $(pagiwrapper).replaceWith(response.pagination);
        } else if (
          response.pagination &&
          Number(response.max_pages) <= 1 &&
          !append
        ) {
          $(pagiwrapper).empty();
        }

        // Update maxPages if provided
        if (typeof response.max_pages !== "undefined") {
          window.maxPages = response.max_pages;
        }

        // Update currentPage and perPage
        currentPage = page;
        const postsPer = $(pagiwrapper).data("posts-per-page");
        if (postsPer) {
          perPage = parseInt(postsPer, 10);
        }
      },
      error: function (xhr, status, error) {
        $(pagiloader).removeClass("loading");

        console.error("AJAX error:", status, error);
      },
      complete: function () {
        if ($(pagiloader)) {
          $(pagiloader).removeClass("ready");
        }
        if ($(cubeabfloader)) {
          $(cubeabfloader).removeClass("ready");
        }
        isLoading = false;
        unlockFilters();
        $(pagiBtn + ", " + pagiloadMore)
          .prop("disabled", false)
          .removeClass("disabled");
      },
    });
  }

  function getFilteredBaseUrl() {
    const { origin, pathname, search } = window.location;

    // Define filter patterns (regex for segments like 'product_cat-true').
    const filterPatterns = [
      /^product_cat-/,
      /^product_tag-/,
      /^product_brand-/,
      /^[a-z0-9_]+_range=/,
      /^pa_[^/]+-/,
      /^meta_[^/]+-/,
      /^category-/,
      /^post_tag-/,
      /^rating-/,
      /^keyword=/,
    ];

    const moreParam = moreUrlPeramVar;

    // Clean pathname: remove /page/N/ and /more/M/ tail.
    let cleanPath = pathname
      .replace(/\/page\/\d+\/?$/, "") // Remove /page/N/
      .replace(new RegExp(`\/${moreParam}\/\d+\/?$`, "i"), ""); // Remove /more/M/

    // Split into segments, filter out filter segments.
    const segments = cleanPath.split("/").filter(Boolean);
    const rootSegments = [];

    segments.forEach((seg) => {
      // Special: If seg starts with 'price_range=', remove it (price in query).
      if (seg.match(/^[a-z0-9_]+_range=/)) return;

      // Check if filter segment.
      const isFilter = filterPatterns.some((pattern) => pattern.test(seg));
      if (!isFilter) {
        rootSegments.push(seg);
      }
    });

    // Rebuild base path (e.g., ['', 'sample-page'] → /sample-page/).
    let basePath = rootSegments.length > 0 ? "/" + rootSegments.join("/") : "";

    // Preserve query params (e.g., ?min_price=...).
    const queryString = search ? search : "";

    return origin + basePath + queryString;
  }
  function escapeRegExp(s) {
    return String(s).replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  }

  function stripPaginationAndMore(path) {
    return path
      .replace(/\/page\/\d+/g, "") // remove /page/X
      .replace(new RegExp(`/${moreUrlPeramVar}/\\d+`, "g"), "") // remove /more/X
      .replace(/\/+/g, "/") // collapse double slashes
      .replace(/\/$/, ""); // remove trailing slash
  }

  function stripDomain(url) {
    return url.replace(/^https?:\/\/[^/]+/i, ""); // remove full domain
  }

  function getUrlTokensForFilter($wrap, val) {
    // For price_range, return null (handled elsewhere)
    // For arrays: return array of tokens
    // For scalar: return [token]
    const out = [];

    // checkboxes/radios
    const $inputs = $wrap.find(
      "input.cubeabf-filter-input:checked:not(:disabled)",
    );
    if ($inputs.length) {
      $inputs.each(function () {
        const tok = String($(this).data("url") ?? $(this).val() ?? "").trim();
        if (tok) out.push(tok);
      });
      return out;
    }

    // selects
    const $sel = $wrap.find("select.cubeabf-filter-input");

    if ($sel.length) {
      // MULTISELECT
      if ($sel.prop("multiple")) {
        $sel.find("option:selected").each(function () {
          const tok = String($(this).data("url") ?? $(this).val() ?? "").trim();

          if (tok) out.push(tok);
        });

        return out;
      }

      // SINGLE SELECT
      const $opt = $sel.find("option:selected");

      const tok = String($opt.data("url") ?? $opt.val() ?? "").trim();

      if (tok) out.push(tok);

      return out;
    }

    // fallback: use given value
    if (Array.isArray(val)) {
      val.forEach((v) => out.push(String(v)));
    } else if (val != null && val !== "") {
      out.push(String(val));
    }

    return out;
  }

  function getRangeValueForUrl(key) {
    const $wrap = $(
      ".cubeabf-filter[data-filter-name='" + CSS.escape(String(key)) + "']",
    ).first();

    if (!$wrap.length) {
      return {
        min: "",
        max: "",
      };
    }

    // Current live limits from AJAX
    const liveMin =
      parseFloat($wrap.attr("data-min")) ||
      parseFloat($wrap.find(".number-range-min").attr("min")) ||
      0;

    const liveMax =
      parseFloat($wrap.attr("data-max")) ||
      parseFloat($wrap.find(".number-range-max").attr("max")) ||
      99999;

    const state = window.cubeabfActiveRanges?.[key];

    // If user interacted with this range
    if (state && state.isDirty) {
      return {
        min: state.minActive === false ? liveMin : state.min,
        max: state.maxActive === false ? liveMax : state.max,
      };
    }

    // Fallback
    return {
      min: liveMin,
      max: liveMax,
    };
  }

  function getUrlKeyForFilter($wrap, fallback) {
    const $input = $wrap.find(".cubeabf-filter-input:checked:first");

    return $input.data("key-url") || fallback;
  }

  const updateFurl = cubeabf_ajax_data.update_f_url || "true";

  function updateUrl(page = 1, isLoadMore = false, loadMoreStartPage = null) {
    const safe = escapeRegExp(moreUrlPeramVar);
    const tailRe = new RegExp("/page/\\d+/(?:" + safe + "/\\d+)?/?$");

    // Remove any existing /page/N/fromthe/M/ from the path
    let basePath = window.location.pathname.replace(tailRe, "");

    // --- NEW: Remove all filter segments like product_cat-xxx or pa_color-yyy ---
    basePath = basePath.replace(
      /\/(rating-\d+(?:\.\d+)?|product_[^/-]+-[^/]+|pa_[^/-]+-[^/]+|meta_[^/-]+-[^/]+|product_tag-[^/]+|category-[^/]+|post_tag-[^/]+|product_brand-[^/]+|[a-z0-9_]+_range=\d+(?:\.\d+)?-\d+(?:\.\d+)?)/g,
      "",
    );

    if (basePath.endsWith("/")) {
      basePath = basePath.slice(0, -1);
    }

    let url;

    // Detect if we're in search mode
    const isSearch =
      window.location.pathname.includes("/search/") ||
      new URLSearchParams(window.location.search).has("s");

    if (isSearch) {
      // --- SEARCH: Use /search/term/page/N/ ---
      const searchMatch =
        window.location.pathname.match(/\/search\/([^/]+)\/?/);
      const searchTerm = searchMatch
        ? searchMatch[1]
        : new URLSearchParams(window.location.search).get("s");

      if (!searchTerm) return; // safety

      url = `${window.location.origin}/search/${decodeURIComponent(
        searchTerm,
      )}/`;

      if (page > 1) url += `page/${page}/`;
      if (isLoadMore && parmore !== "false" && loadMoreStartPage !== null) {
        url += `${moreUrlPeramVar}/${loadMoreStartPage}/`;
      }
    } else {
      // --- NORMAL PAGE: Use /page/N/ ---
      url = `${basePath}/`;

      if (page > 1) {
        url += `page/${page}/`;
      } else {
        url = basePath + "/";
      }

      if (isLoadMore && parmore !== "false" && loadMoreStartPage !== null) {
        url += `${moreUrlPeramVar}/${loadMoreStartPage}/`;
      }
    }

    window.history.replaceState({ page }, "", url);
  }

  let loadMoreOriginPage = null;

  /* --------------------------------------------------------------
   Pagination click – SEARCH PAGE ONLY
   -------------------------------------------------------------- */
  if (enablePagination === "true" && ajaxoff === "false") {
    $(document).on("click", pagiBtn, function () {
      const page = parseInt($(this).data("page"), 10);
      const pperPage = cubeabf_ajax_data.pppage || 6;
      const grid = $("." + qselect).closest("." + wrapclass);
      // Reset state
      currentPage = page;
      loadedPages = [page];
      loadMoreOriginPage = page;

      // Reset "after" tracking when clicking pagination
      moreStartPage = null;
      isMoreSequence = false;
      grid.addClass("load");
      $(pagiloader).addClass("ready");
      // Load posts
      loadPosts(currentPage, false, pperPage).then(() => {
        // Update URL: page click → remove `more` param
        if (updateurl === "true") {
          updateUrl(currentPage, false, null); // ← explicitly pass null
        }
      });
    });
  }

  /* --------------------------------------------------------------
   Helper – extract the term from a pretty search URL
   -------------------------------------------------------------- */
  function getSearchTermFromUrl() {
    const match = window.location.pathname.match(/\/search\/([^/]+)\/?/);
    return match ? decodeURIComponent(match[1]) : "";
  }

  function parseMore() {
    const match = window.location.pathname.match(
      new RegExp(`/page/(\\d+)/${moreUrlPeramVar}/(\\d+)/?`),
    );
    return match
      ? {
          targetPage: parseInt(match[1], 10),
          startPage: parseInt(match[2], 10),
        }
      : null;
  }

  function getthetPage() {
    const moreParams = parseMore();
    return moreParams
      ? moreParams.targetPage
      : parseInt(window.location.pathname.match(/\/page\/(\d+)/)?.[1], 10) || 1;
  }

  if (enableLoadMore === "true") {
    $(document)
      .off("click", pagiloadMore)
      .on("click", pagiloadMore, function () {
        if (!enableLoadMore) return;

        // Ensure loadedPages is array and has currentPage
        if (!Array.isArray(loadedPages) || loadedPages.length === 0) {
          loadedPages = [currentPage];
        }
        const nextPage = Math.max(...loadedPages, currentPage) + 1;
        const pperPage = parseInt(cubeabf_ajax_data.pppage, 10) || 6;
        $(cubeabfloader).addClass("ready");
        // === 1. Get "after" number from URL (e.g., /fromthe/5/) ===
        const urlMoreNumber = getMoreNumberFromPath();

        // === 2. Set loadMoreOriginPage ONCE when Load More starts ===
        if (loadMoreOriginPage === null) {
          loadMoreOriginPage = urlMoreNumber ?? currentPage;
        }

        // === 3. Mark that we're in "load more" sequence ===
        isMoreSequence = true;

        // === 4. Load posts ===
        const loadPromise = loadPosts(nextPage, true, pperPage);

        // === 5. Update state & URL after load ===
        if (loadPromise && typeof loadPromise.then === "function") {
          loadPromise
            .then(() => {
              // Update tracking
              if (!loadedPages.includes(nextPage)) {
                loadedPages.push(nextPage);
              }
              currentPage = nextPage;

              // Update URL: keep "after" param
              if (updateurl === "true") {
                updateUrl(nextPage, true, loadMoreOriginPage);
              }
            })
            .catch((e) => {
              console.error("Load more AJAX failed:", e);
            });
        } else {
          // Fallback for non-promise
          if (!loadedPages.includes(nextPage)) loadedPages.push(nextPage);
          currentPage = nextPage;
          if (updateurl === "true") {
            updateUrl(nextPage, true, loadMoreOriginPage);
          }
        }
      });
  }

  let cubeabfUserScrolled = false;
  $(window).on("scroll.cubeabfUser", function () {
    cubeabfUserScrolled = true;
  });

  // Initialize infinite scroll
  if (
    (infinite === "true" || infinite !== false) &&
    enableInfinite !== "false"
  ) {
    // ✅ keep state OUTSIDE so it doesn't reset on every scroll
    let isLoading = false;

    function scrollHandler() {
      if (!cubeabfUserScrolled) return;

      if (isLoading || infinite === "false" || infinite === false) return;

      function getDynamicThreshold() {
        const vh = window.innerHeight;

        // Always at least 300px, at most 1200px
        // Scales naturally with screen size
        return Math.min(Math.max(vh * 0.8, 300), 1200);
      }

      // Update on resize/orientation change
      let scrollThreshold = getDynamicThreshold();

      function updateThreshold() {
        scrollThreshold = getDynamicThreshold();
      }

      window.addEventListener("resize", updateThreshold);
      window.addEventListener("orientationchange", updateThreshold);

      const scrollTop = window.scrollY;
      const windowHeight = window.innerHeight;
      const documentHeight = document.documentElement.scrollHeight;

      if (scrollTop + windowHeight >= documentHeight - scrollThreshold) {
        isLoading = true;

        currentPage = getCurrentPageFromUrl();
        const nextPage = Math.max(...loadedPages, currentPage || 1) + 1;
        const pperPage = parseInt(cubeabf_ajax_data.pppage, 10) || 6;

        let urlMoreNumber = null;
        try {
          urlMoreNumber = getMoreNumberFromPath();
        } catch (e) {
          urlMoreNumber = null;
        }

        if (loadMoreOriginPage === null) {
          loadMoreOriginPage =
            urlMoreNumber !== null ? urlMoreNumber : currentPage || 1;
        }

        jQuery(cubeabfloader).addClass("ready");

        // ✅ Always treat loadPosts as promise-like
        Promise.resolve(loadPosts(nextPage, true, pperPage))
          .then((response) => {
            if (!response || !response.html || response.html.trim() === "") {
              infinite = "false";
              // ✅ ONLY unbind your own handlers

              return;
            }

            if (!loadedPages.includes(nextPage)) loadedPages.push(nextPage);
            currentPage = nextPage;

            if (updateurl === "true" && infinite === "true") {
              updateUrl(nextPage, true, loadMoreOriginPage);
            }

            // ✅ after DOM append, mobile might still be near bottom → check again
            setTimeout(scrollHandler, 0);
          })
          .catch((e) => {
            console.error("AJAX failed:", e);
          })
          .finally(() => {
            isLoading = false;
          });
      }
    }

    // ✅ Bind ONCE (namespaced, but simple)
    jQuery(window)
      .off("scroll.cubeabf touchmove.cubeabf") // remove only ours
      .on("scroll.cubeabf touchmove.cubeabf", scrollHandler)
      .off("resize.cubeabf")
      .on("resize.cubeabf", scrollHandler);

    // ✅ initial check (important on mobile)
    setTimeout(scrollHandler, 0);
  }

  window.cubeabfRefreshInfinite = function () {
    setTimeout(() => {
      jQuery(window).trigger("resize.cubeabf");
      jQuery(window).trigger("scroll.cubeabf");
      // second tick helps after DOM height changes settle
      setTimeout(() => jQuery(window).trigger("scroll.cubeabf"), 80);
    }, 0);
  };

  let priceChangeTimeout;

  // Listen to all cubeabf filter inputs
  $(document).on(
    "click input change",
    ".cubeabf-load-posts, .cubeabf-filter",
    function (e) {
      // 3. Внутри функции ПРОВЕРЯЕМ, должно ли текущее событие сработать
      if (initurl === true || loadBtn !== "true") {
        // Если initurl активен ИЛИ режим кнопок выключен -> обрабатываем ТУЛЬКО фильтры
        if (!$(this).hasClass("cubeabf-filter")) return;
      } else {
        // Если initurl выключен И loadBtn активен -> обрабатываем ТОЛЬКО кнопку загрузки
        if (!$(this).hasClass("cubeabf-load-posts")) return;
      }
      const $target = $(e.target);
      const $wrapf = $(this);

      // Ignore clicks on normal filter wrappers.
      // Allow live search wrapper to continue.
      if (
        $(this).hasClass("cubeabf-filter") &&
        !$(this).hasClass("cubeabf-live-search") &&
        !$target.is(
          ".cubeabf-filter-input, select, .number-range-min, .number-range-max, [data-display='number-min'], [data-display='number-max']",
        )
      ) {
        return;
      }

      if (e.type === "click" && $target.is("select")) {
        return;
      }
      // Search filter is special
      if ($wrapf.data("filter-type") === "keyword") {
        if (!window.cubeabfSearchTriggered) {
          return;
        }

        // consume trigger
        window.cubeabfSearchTriggered = false;
      }

      if ($target.length && $target.is(":disabled")) {
        return false;
      }
      if (window.cubeabfIsUpdatingLimits) {
        return false;
      }

      if (window.cubeabfIsDraggingRange && e.type === "input") {
        return false;
      }

      if (window.cubeabfIsDraggingKnob && e.type === "input") {
        return false;
      }

      if (updateurl === "true") {
        updateUrl(1);
      }

      isLoading = false;
      const grid = $("." + qselect).closest("." + wrapclass);

      grid.addClass("load");
      $(pagiloader).addClass("ready");

      // Trigger AJAX filter refresh
      clearTimeout(priceChangeTimeout);
      priceChangeTimeout = setTimeout(() => {
        const pperPage = cubeabf_ajax_data.pppage || 6;

        if (updateurl === "true" && !initurl) {
          updateUrl(currentPage, false, null);
        }
        loadPosts(currentPage, false, pperPage)
          .then(() => {
            // Reset state
            infinite = cubeabf_ajax_data.infinite || "false";
            currentPage = 1;
            loadedPages = [1];
            loadMoreOriginPage = null;
            // Scroll to top to ensure consistent scroll position
            if (
              (infinite === "true" || infinite !== false) &&
              enableInfinite !== "false"
            ) {
              jQuery(window).scrollTop(0);
            }
          })
          .catch((e) => {
            console.error("Filter: loadPosts failed:", e); // Debug
          });
      }, 300);
    },
  );

  // Replace this with your real "refresh" function
  function cubeabfApplyFilters() {
    // Call your existing AJAX / update routine here.
    // e.g. cubeabfFetchProducts(filters);
    $(document).trigger("cubeabf:filtersChanged", [filters]);
  }

  /**
   * Принудительно запускает фильтрацию на основе текущего состояния кнопки загрузки.
   *
   * @param {string} loadBtn - Флаг кнопки ("true" или "false").
   * @param {string} eventType - Тип события для триггера (например, "change" или "click").
   * @param {string} selector - Селектор по умолчанию, если активных фильтров нет.
   */
  function cubeabfTriggerFilters(loadBtn, eventType, selector) {
    // Защита: прерываем выполнение, если кнопка загрузки не активна
    if (loadBtn !== "true") {
      return;
    }

    // Очищаем eventType от множественных событий (например, "input change" -> "change")
    // чтобы избежать двойного срабатывания триггера
    const singleEvent = eventType.split(" ")[0];

    // Ищем первый заполненный чекбокс, радиокнопку или текстовый инпут
    const $activeFilter = $(".cubeabf-filter")
      .filter(function () {
        return (
          this.checked ||
          (this.value && this.type !== "checkbox" && this.type !== "radio")
        );
      })
      .first();

    // Если нашли активный элемент — триггерим его, иначе — берем дефолтный селектор
    if ($activeFilter.length) {
      $activeFilter.trigger(singleEvent);
    } else if (selector) {
      $(selector).first().trigger(singleEvent);
    }
  }

  function parseStarsSetting(raw) {
    const s = String(raw ?? "1,1").trim(); // default: text, no empties
    const parts = s.split(",").map((x) => x.trim());

    const mode = parts[0] || "1"; // "1" text, "2" svg
    const empty = parts[1] || "1"; // "1" no, "2" yes

    return {
      mode,
      showEmpty: empty === "2",
    };
  }

  function cubeabfStarsText(rating, max = 5, showEmpty = false) {
    rating = Math.max(0, Math.min(parseInt(rating, 10) || 0, max));
    return showEmpty
      ? "★".repeat(rating) + "☆".repeat(max - rating)
      : "★".repeat(rating);
  }

  window.cubeabfStars = window.cubeabfStars || {};

  function cubeabfStarSvgFilled() {
    return (
      window.cubeabfStars.filled ||
      `
    <svg class="rating-star filled" viewBox="0 0 24 24" aria-hidden="true">
      <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24
               l-7.19-.61L12 2 9.19 8.63 2 9.24
               l5.46 4.73L5.82 21z"></path>
    </svg>`
    );
  }

  function cubeabfStarSvgEmpty() {
    return (
      window.cubeabfStars.empty ||
      `
    <svg class="rating-star empty" viewBox="0 0 24 24" aria-hidden="true">
      <path d="M22 9.24l-7.19-.62L12 2
               9.19 8.63 2 9.24l5.46 4.73
               L5.82 21 12 17.27 18.18 21
               l-1.64-7.03L22 9.24z"
            fill="none" stroke-width="2"></path>
    </svg>`
    );
  }

  function cubeabfStarsSvg(rating, max = 5, showEmpty = false) {
    rating = Math.max(0, Math.min(parseInt(rating, 10) || 0, max));
    return showEmpty
      ? cubeabfStarSvgFilled().repeat(rating) +
          cubeabfStarSvgEmpty().repeat(max - rating)
      : cubeabfStarSvgFilled().repeat(rating);
  }

  function normalizeRating(raw, $ctx = null, max = 5) {
    // raw can be: "4", 4, "4+", "4_and_up", "rating-4", ["4"], etc.
    let s = raw;

    if (Array.isArray(s)) s = s[0];
    s = s == null ? "" : String(s).trim();

    // 1) Direct digit parse anywhere in string: "4", "4+", "rating-4", "4_and_up"
    const m = s.match(/(\d+)/);
    if (m) {
      const n = parseInt(m[1], 10);
      if (!isNaN(n)) return Math.max(0, Math.min(n, max));
    }

    // 2) If we have a context element (label/option), count stars there
    if ($ctx && $ctx.length) {
      // SVG stars (filled)
      const svgCount = $ctx.find("svg.rating-star.filled").length;
      if (svgCount) return Math.max(0, Math.min(svgCount, max));

      // Text stars like ★★★★
      const txt = $ctx.text ? String($ctx.text()) : "";
      const filled = (txt.match(/★/g) || []).length;
      if (filled) return Math.max(0, Math.min(filled, max));
    }

    return 0;
  }

  function parseStarsLabelMap(raw) {
    const map = {};
    raw = String(raw ?? "").trim();
    if (!raw || raw === "false") return map;

    raw.split("|").forEach((pair) => {
      pair = pair.trim();
      if (!pair) return;

      const idx = pair.indexOf(",");
      if (idx === -1) return;

      const k = pair.slice(0, idx).trim(); // "5"
      const v = pair.slice(idx + 1).trim(); // "/5+"
      if (!k || !v) return;

      map[String(k)] = v;
    });

    return map;
  }

  function cubeabfIsPlaceholderSelection($wrap, value) {
    const $sel = $wrap.find("select.cubeabf-filter-input").first();
    if (!$sel.length) return false;

    const $opt = $sel
      .find("option")
      .filter(function () {
        return String($(this).val()) === String(value);
      })
      .first();

    if (!$opt.length) return false;

    return (
      $opt.hasClass("cubeabf-default-option") ||
      String($opt.data("placeholder")) === "1" ||
      String($opt.attr("data-placeholder")) === "1" ||
      String($opt.val()) === "" ||
      $opt.prop("disabled") === true
    );
  }
});

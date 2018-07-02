const cheerio = require('cheerio');

getLinks = (html, context, linkSelector, breakInParts, noOfParts, baseUrl) => {
  let selector = context + " " + linkSelector;
  let links = [];

  const $ = cheerio.load(html);
  try {
    $(selector).each(function(i, elem) {
      if (breakInParts && i >= noOfParts) {
        return;
      } else {
        link = $(elem).attr('href');
        if (baseUrl != "") {
          link = baseUrl + link;
        }
        links.push(link);
      }
    });
    return links;
  } catch (err) {
    console.log(err);
  }
}



appendInnerPage = (html, selector, pages, innerPageSelector, breakInParts, noOfParts) => {
  const $ = cheerio.load(html);
  // Remove unwanted scripts tags
  $('script').remove();
  let newHtml = "";
  let leftHtml = "";
  let left = 0;
  try {
    $(selector).each(function(i, elem) {
      // check
      if (breakInParts && i >= noOfParts) {
        /* if greater than divison section in case of break in parts enabled
          store the remaining HTML to send back to PHP as a response so as to
          avoid fetching the HTML again.
        */
        leftHtml = leftHtml + $.html(elem);
        left += 1;
      } else {
        /* Append the inner page content to selector elements like -
          <affiliatesconnect>inner page selected content</affiliatesconnect>
          to each of the elements.
         */
        const $$ = cheerio.load(pages[i]);
        $$('script').remove();
        $(this).append('<affiliatesconnect>' + $$.html(innerPageSelector) + '</affiliatesconnect>');
        newHtml += $.html(elem);
      }
    });
    return [newHtml, leftHtml, left];
  } catch (err) {
    console.log(err);
  }
}

exports.getLinks = getLinks;
exports.appendInnerPage = appendInnerPage;

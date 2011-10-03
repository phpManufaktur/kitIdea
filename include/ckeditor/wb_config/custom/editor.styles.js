/**
 *  @module         ckeditor
 *  @version        see info.php of this module
 *  @authors        Michael Tenschert, Dietrich Roland Pehlke
 *  @copyright      2010-2011 Michael Tenschert, Dietrich Roland Pehlke
 *  @license        GNU General Public License
 *  @license terms  see info.php of this module
 *  @platform       see info.php of this module
 *  @requirements   PHP 5.2.x and higher
 */
 
 CKEDITOR.addStylesSet( 'wb',
[
    /* Block Styles */

    // These styles are defined per editor.css in your template and can be found in "Format".
    /*
    { name : 'Paragraph'        , element : 'p' },
    { name : 'Heading 1'        , element : 'h1' },
    { name : 'Heading 2'        , element : 'h2' },
    { name : 'Heading 3'        , element : 'h3' },
    { name : 'Heading 4'        , element : 'h4' },
    { name : 'Heading 5'        , element : 'h5' },
    { name : 'Heading 6'        , element : 'h6' },
    { name : 'Preformatted Text', element : 'pre' },
    { name : 'Address'            , element : 'address' },
    */

    { name : 'Breaking Div'     , element : 'div', styles : {'clear' : 'both', 'height' : '1px', 'overflow' : 'hidden'} },
    { name : 'H3 Blue Title'    , element : 'h3',  styles : { 'color' : 'Blue' } },
    { name : 'H3 Red Title'     , element : 'h3',  styles : { 'color' : 'Red' } },

    /* Inline Styles */

    // These are core styles available as toolbar buttons. You may opt enabling
    // some of them in the Styles combo, removing them from the toolbar.

    // Nops, enabled  Strong and Emphasis as i changed Default behavior of  B and I Button
    { name : 'Strong'           , element : 'strong', overrides : 'b' },
    { name : 'Emphasis'         , element : 'em'    , overrides : 'i' },
    /*
    { name : 'Underline'        , element : 'u' },
    { name : 'Strikethrough'    , element : 'strike' },
    { name : 'Subscript'        , element : 'sub' },
    { name : 'Superscript'      , element : 'sup' },
    */
    { name : 'Br Clear'         , element : 'br', styles : { 'clear' : 'both'}},
    
    // Nops, we need some examples for Multiple settings on tags.
    { name : 'Marker: Yellow'   , element : 'span', styles : { 'background-color' : 'Yellow' }, attributes : { 'class' : 'Yellow Marker' }},
    { name : 'Marker: Green'    , element : 'span', styles : { 'background-color' : 'Lime', 'color' : 'White' }},

    { name : 'Big'              , element : 'big' },
    { name : 'Small'            , element : 'small' },
    { name : 'Typewriter'       , element : 'tt' },

    { name : 'Computer Code'    , element : 'code' },
    { name : 'Keyboard Phrase'  , element : 'kbd' },
    { name : 'Sample Text'      , element : 'samp' },
    { name : 'Variable'         , element : 'var' },

    { name : 'Deleted Text'     , element : 'del' },
    { name : 'Inserted Text'    , element : 'ins' },

    { name : 'Cited Work'       , element : 'cite' },
    { name : 'Inline Quotation' , element : 'q' },

    { name : 'Language: RTL'    , element : 'span', attributes : { 'dir' : 'rtl', 'class' : 'the_RTL'} },
    { name : 'Language: LTR'    , element : 'span', attributes : { 'dir' : 'ltr' } },

    /* Object Styles */
    //  This styles are only available when you select the defined objects. E.g. when selecting an image 
    //  you can control here with the styles dropdown the styling.
    {
        name : 'Image on Left',
        element : 'img',
        attributes :
        {
            'style' : 'padding: 5px; margin-right: 5px',
            'border' : '2',
            'align' : 'left'
	    /*'class' : 'leftimage'*/ // You can define classes here to avoid generating too many style commandos inside of your HTML(Spagetti code is bad for SEO)
        }
    },

    {
        name : 'Image on Right',
        element : 'img',
        attributes :
        {
            'style' : 'padding: 5px; margin-left: 5px',
            'border' : '2',
            'align' : 'right'
	    /*'class' : 'rightimage'*/ // You can define classes here to avoid generating too many style commandos inside of your HTML(Spagetti code is bad for SEO)

        }
    },

    // Here you can define Divbox classes as well these are shown on "Create div Container"
    {
        name : 'Gray Divbox',
        element : 'div',
        attributes :
        {
            'style' : 'background: #eeeeee; margin: 5px; border: 3px solid #cccccc ',
            'border' : '2',
            'align' : 'right'
	    /*'class' : 'rightimage'*/ // You can define classes here to avoid generating too many style commandos inside of your HTML(Spagetti code is bad for SEO)

        }
    }
]);
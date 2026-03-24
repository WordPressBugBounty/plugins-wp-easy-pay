const { registerBlockType } = wp.blocks;

var wpep_block_container = {
	"text-align": 'center'
};

var divStyle = {
	"margin-bottom": "15px",
};

var wpep_logo = {
	width: "250px"
}

var wpep_select = {
	width: "50%",
	"font-size": "14px",
	border: "1px solid #dbdbdb",
	padding: "5px 15px",
	"font-family": "Figtree"
}

const pluginUrl = ( typeof wpep_data !== 'undefined' && wpep_data.plugin_url ) ? wpep_data.plugin_url : '';
const wpep_icon = pluginUrl ? `${pluginUrl}assets/backend/img/wpep-logo-pro.png` : null;
const wpep_icon_element = wpep_icon ? wp.element.createElement(
	'img',
	{
		src: wpep_icon,
		style: {
			width: '60px',
			height: '15px'
		}
	}
) : 'format-aside';


registerBlockType(
	'wpep/shortcode',
	{

		title: 'WP Easy Pay Form',
		description: 'Block to add WP EASY PAY shortcode to the page',
		icon: wpep_icon_element,
		category: 'layout',
		attributes: {
			type: {
				type: 'string',

			}
		},
		edit( props ) {


			var p       = ( typeof wpep_forms !== 'undefined' && wpep_forms.forms ) ? wpep_forms.forms : [];
			var options = [];

			options.push( < option value = "" > Please select your form < / option > )

			for (var key in p) {

				if (p.hasOwnProperty( key )) {
					var form_id    = p[key].ID;
					var form_title = p[key].title;
					if (props.attributes.type == form_id) {

						options.push( < option value = {form_id} selected > {form_title} < / option > )

					} else {

						options.push( < option value = {form_id} > {form_title} < / option > )
					}
				}

			}

			var type = props.attributes.type;

			function wpep_shortcode_change(e) {
				var form_id = e.target.value;
				props.setAttributes( {type: form_id} );
			}

			const logoSrc = pluginUrl ? `${pluginUrl}assets/backend/img/wpep-logo-pro.png` : '';

			return (
			<div style = {wpep_block_container}> <div style = {divStyle} > <img style={wpep_logo} src={logoSrc}/> </div> <div> <select style={wpep_select} onChange={wpep_shortcode_change}>{options} </select> </div> </div>
			);

		},
		save( props ) {
			return null;
		}

	}
);

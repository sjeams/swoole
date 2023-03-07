
module.exports = {
	devServer:{
		port:'8080',
		disableHostCheck:true,
	    https: false,
		proxy:{
			'/api':{
				target:'http://cs.mheart.xyz',
				changeOrigin:true,
				pathRewrite:{
					// '^/dpc': ''
				}
			}
		}
	},
}

 
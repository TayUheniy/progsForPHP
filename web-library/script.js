function sendform() {
	let flag = true;
	let nameId = ["namebook", "authorbook", "yearcreate","annotation","ganre", "yearread"];
	for (let i = 0; i < nameId.length; ++i){
		if (document.getElementById(nameId[i]).value == "") {
			document.getElementById(nameId[i]).style.backgroundColor="red";
			if (nameId[i] == "yearcreate" || nameId[i] == "yearread") {
				document.getElementById(nameId[i]).placeholder="Заполните данное поле числами";	
			}
			else {
				document.getElementById(nameId[i]).placeholder="Заполните данное поле, если хотите добавить";
			}
			flag = false;
		}
		else
		{
			if ((nameId[i] == "yearcreate" || nameId[i] == "yearread") && (document.getElementById(nameId[i]).value < -1000 || document.getElementById(nameId[i]).value > 2020)){
				document.getElementById(nameId[i]).style.backgroundColor="red";
				document.getElementById(nameId[i]).value="";
				document.getElementById(nameId[i]).placeholder="от -1000 до 2020";
				flag = false;	
			}
			else {
				if  (nameId[i] == "yearcreate") {
					document.getElementById(nameId[i]).style.backgroundColor="white";
				}
				if  (nameId[i] == "annotation") {
					document.getElementById(nameId[i]).style.backgroundColor="white";
				}
				if  (nameId[i] == "yearread") {
					document.getElementById(nameId[i]).style.backgroundColor="white";
				}
				if (nameId[i] == "namebook") {
					if (/^[A-ZА-Я]|^\d/.test(document.getElementById(nameId[i]).value)) {
						document.getElementById(nameId[i]).style.backgroundColor="white";
					}
					else {
						document.getElementById(nameId[i]).style.backgroundColor="red";
						flag = false;
					}
				}
				
					if (nameId[i] == "authorbook") {
					if (/^[A-ZА-Я][а-яa-z]+ [A-ZА-Я][а-яa-z]+/.test(document.getElementById(nameId[i]).value)) {
						document.getElementById(nameId[i]).style.backgroundColor="white";
					}
					else {
						document.getElementById(nameId[i]).style.backgroundColor="red";
						flag = false;
					}
				}
				
				if (nameId[i] == "ganre") {
					if (/^[а-яa-z]+( [а-яa-z]+)*/.test(document.getElementById(nameId[i]).value)) {
						document.getElementById(nameId[i]).style.backgroundColor="white";
					}
					else {
						document.getElementById(nameId[i]).style.backgroundColor="red";
						flag = false;
					}
				}
			}
		}
	}
	return flag;
}

function searchform() {
	let count = 0;
	let nameId = ["namebook", "authorbook", "yearcreate","annotation","ganre", "yearread"];
	for (let i = 0; i < nameId.length; ++i){
		if (document.getElementById(nameId[i]).value == "") {
			++count;
		}
	}
	if (count != nameId.length)
	{
		return true;
	}
	else {
		for (let i = 0; i < nameId.length; ++i){
			document.getElementById(nameId[i]).style.backgroundColor="orange";
			document.getElementById(nameId[i]).placeholder="Заполните хотя бы одно поле";
		}
		return false;
	}
}
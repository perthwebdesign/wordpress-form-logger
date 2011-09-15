<form method="post" action="/saveform/survey">
	<fieldset>
		<legend>Personal Details</legend>
		<div>
			<label for="Firstname">Firstname</label>
			<input type="text" name="Firstname" id="Firstname" />
		</div>
		
		<div>
			<label for="Lastname">Lastname</label>
			<input type="text" name="Lastname" id="Lastname" />
		</div>
		
		<div>
			<label for="Address">Address</label>
			<input type="text" name="Address" id="Address" />
		</div>
	</fieldset>
	
	<fieldset>
		<legend>Survey Questions</legend>
		
		<fieldset>
			<legend>Did you enjoy filling out this form?</legend>
			<ul>
				<li>
					<label for="Enjoyment-yes">Sure did</label>
					<input type="radio" name="Enjoyment" id="Enjoyment-yes" value="Yes" />
				</li>
				<li>
					<label for="Enjoyment-no">Heck no</label>
					<input type="radio" name="Enjoyment" id="Enjoyment-no" value="No" />
				</li>
			</ul>
		</fieldset>
		
		<div>
			<label for="Improvements">Let us know of any improvements to make</label>
			<input type="text" name="Improvements" id="Improvements" />
		</div>
	</fieldset>
	
	<fieldset>
		<label for="Submit">Send us your feedback</label>
		<input type="submit" name="Submit" id="Submit" />
	</fieldset>
</form>
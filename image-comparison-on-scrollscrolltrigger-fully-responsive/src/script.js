gsap.utils.toArray(".comparisonSection").forEach(section => {
	let tl = gsap.timeline({
			scrollTrigger: {
				trigger: section,
				start: "center center",
        // makes the height of the scrolling (while pinning) match the width, thus the speed remains constant (vertical/horizontal)
				end: () => "+=" + section.offsetWidth, 
				scrub: true,
				pin: true,
        anticipatePin: 1
			},
			defaults: {ease: "none"}
		});
	// animate the container one way...
	tl.fromTo(section.querySelector(".afterImage"), { xPercent: 100, x: 0}, {xPercent: 0})
	  // ...and the image the opposite way (at the same time)
	  .fromTo(section.querySelector(".afterImage img"), {xPercent: -100, x: 0}, {xPercent: 0}, 0);
});


// 💚 This just adds the GSAP link to this pen, don't copy this bit
import { GSAPInfoBar } from "https://codepen.io/GreenSock/pen/vYqpyLg.js"
new GSAPInfoBar({ link: "https://gsap.com/docs/v3/Plugins/ScrollTrigger/" });
// 💚 Happy tweening!
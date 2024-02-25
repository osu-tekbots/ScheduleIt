$(function () {
  calendarInit();
});

function calendarInit() {
  const calendarElement = document.getElementById("calendar");

  if (calendarElement) {
    const events = $("#calendar").data("calendar-events");
    const siteUrl = $("#calendar").data("site-url");
    const userId = $("#calendar").data("user-id");

    const mappedEvents = events.map(function (event) {
      const createdEvent = event.creator_id === parseInt(userId, 10);
      const meetingWith = createdEvent ? event.booker_name : event.creator_name;

      return {
        classNames:
          createdEvent
            ? ["calendar-meeting-mine"]
            : ["calendar-meeting-other"],
        title: event.name + "\n(" + meetingWith + ")",
        url:
          createdEvent
            ? `${siteUrl}/meetings/${event.id}`
            : `${siteUrl}/invite?key=${event.meeting_hash}`,
        start: event.start_time,
      };
    });

    const calendar = new FullCalendar.Calendar(calendarElement, {
      events: mappedEvents,
      initialView: "dayGridMonth",
      headerToolbar: {
        left: 'dayGridMonth,listWeek',
        center: 'title'        
      },

      eventContent: (object) => {
        sections = object.event.title.split("\n");

        return {html: 
          object.timeText + " " 
            + "<b>" + sections[0] + "</b>" 
            + "<br/>&emsp;" + sections[1]};
      }
    });
    calendar.render();
  }
}

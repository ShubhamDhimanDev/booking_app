import React from 'react';
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, useForm } from "@inertiajs/inertia-react";
import InputLabel from "@/Components/InputLabel";
import TextInput from "@/Components/TextInput";
import InputError from "@/Components/InputError";
import PrimaryButton from "@/Components/PrimaryButton";
import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';

export default function EventsShowPublic({ event, availableSlots = [], bookedSlots = {} }) {
  const { data, post, setData, processing, errors, reset } = useForm({
    booked_at_date: "",
    booked_at_time: "",
    booker_name: "",
    booker_email: "",
  });

  const onHandleChange = (event) => {
    setData(
      event.target.name,
      event.target.type === "checkbox"
        ? event.target.checked
        : event.target.value
    );
  };

  const onHandleSubmit = (e) => {
    e.preventDefault();
    post(route("bookings.store", event), {
      onSuccess: () => reset(),
    });
  };

  const today = new Date();
  const availableFromDate = new Date(event.available_from_date);
  const minDate =
    today > availableFromDate
      ? today.toISOString().split("T")[0]
      : event.available_from_date;



  // Prepare availableDates for datepicker, optionally filtered by event.available_week_days
  const weekNames = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
  const allowedWeekDays = Array.isArray(event.available_week_days) && event.available_week_days.length ? event.available_week_days.map(d => d.toString().toLowerCase()) : null;

  const availableDates = availableSlots
    .map((s) => new Date(s.date + 'T00:00:00'))
    .filter(d => {
      if (!allowedWeekDays) return true;
      const wd = weekNames[d.getDay()];
      return allowedWeekDays.includes(wd);
    });

  // selected date state (Date object) — start with no date selected so calendar is shown first
  const [selectedDate, setSelectedDate] = React.useState(null);

  // format a Date object to local YYYY-MM-DD (avoid timezone shifts with toISOString)
  const formatDate = (d) => {
    if (!d) return '';
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  };

  const dateStr = selectedDate ? formatDate(selectedDate) : '';

  // Use per-date timeslots from availableSlots when available, otherwise fall back to event.timeslots
  const timesForDate = React.useMemo(() => {
    const ds = dateStr;
    const slot = availableSlots.find(s => s.date === ds);
    if (slot && slot.timeslots) return slot.timeslots;
    return event.timeslots || [];
  }, [dateStr, availableSlots, event.timeslots]);

  // removed badge rendering to keep calendar minimal (only enable available dates)

  return (
    <AuthenticatedLayout hideNav={true}>
      <Head title={event.title} />

      <div className="-m-4 flex-1 md:flex items-stretch">
        <section className="flex-1 p-8">
          <div className="flex items-center mb-2">
            <img
              src={event.user.avatar}
              alt={event.user.name}
              className="h-8 w-8 rounded-full mr-2"
            />
            <h3 className="text-lg font-bold">{event.user.name}</h3>
          </div>
          <h1 className="text-2xl font-bold">{event.title}</h1>
          <span className="inline-flex items-center text-gray-600 font-semibold mt-1">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              strokeWidth={1.5}
              stroke="currentColor"
              className="w-5 h-5 mr-2"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            {event.duration} minutes
          </span>
          <p>{event.description}</p>
        </section>
        <section className="flex-1 p-8 border-t md:border-t-0 md:border-l border-gray-300">
          <h2 className="mb-2 font-bold text-xl">Book your slot</h2>

          <form action="" onSubmit={(e) => {
              e.preventDefault();
              // transfer selected date to form data
              setData('booked_at_date', dateStr);
              post(route("bookings.store", event), {
                onSuccess: () => reset(),
              });
            }} className="space-y-4">
            <div>
              <InputLabel forInput="booked_at_date" value="Date" />
              <div className="mt-1">
                <DatePicker
                  selected={selectedDate}
                  onChange={(d) => {
                    setSelectedDate(d);
                    const ds = d ? formatDate(d) : '';
                    setData('booked_at_date', ds);
                    // reset previously selected time when changing date
                    setData('booked_at_time', '');
                  }}
                  includeDates={availableDates}
                  minDate={new Date(minDate + 'T00:00:00')}
                  maxDate={new Date(event.available_to_date + 'T00:00:00')}
                  dateFormat="yyyy-MM-dd"
                  className="block w-full border rounded px-2 py-1"
                  placeholderText="Select a date"
                  required
                />
              </div>
              <InputError message={errors.booked_at_date} className="mt-2" />
            </div>

            {selectedDate && (
            <div>
              <InputLabel forInput="booked_at_time" value="Time" />

              <div className="mt-2 grid grid-cols-2 gap-2">
                {timesForDate.map((item, idx) => {
                  const bookedForDate = bookedSlots[dateStr] || [];
                  const disabled = bookedForDate.includes(item.start);
                  const selected = data.booked_at_time === item.start;

                  return (
                    <button
                      key={idx}
                      type="button"
                      onClick={() => {
                        if (!disabled) setData('booked_at_time', item.start);
                      }}
                      disabled={disabled}
                      className={`px-3 py-2 rounded border text-left ${disabled ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : selected ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-800 hover:bg-gray-50'}`}
                      aria-pressed={selected}
                      aria-disabled={disabled}
                    >
                      <div className="font-medium">{item.start} - {item.end}</div>
                      {disabled && <div className="text-xs text-gray-500">Booked</div>}
                    </button>
                  );
                })}
              </div>

              <InputError message={errors.booked_at_time} className="mt-2" />
            </div>
            )}

            <div>
              <InputLabel forInput="booker_name" value="Your Name" />

              <TextInput
                id="booker_name"
                name="booker_name"
                value={data.booker_name}
                className="mt-1 block w-full"
                handleChange={onHandleChange}
              />

              <InputError message={errors.booker_name} className="mt-2" />
            </div>

            <div>
              <InputLabel forInput="booker_email" value="Your Email" />

              <TextInput
                id="booker_email"
                type="email"
                name="booker_email"
                value={data.booker_email}
                className="mt-1 block w-full"
                handleChange={onHandleChange}
              />

              <InputError message={errors.booker_email} className="mt-2" />
            </div>

            <PrimaryButton className="w-full" disabled={processing}>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                strokeWidth={1.5}
                stroke="currentColor"
                className="w-5 h-5 mr-1"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  d="M4.5 12.75l6 6 9-13.5"
                />
              </svg>
              Confirm Booking
            </PrimaryButton>
          </form>
        </section>
      </div>
    </AuthenticatedLayout>
  );
}
